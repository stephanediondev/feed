<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Entity\Feed;
use App\Form\Type\FeedType;
use App\Form\Type\ImportOpmlType;
use App\Manager\ActionFeedManager;
use App\Manager\ActionManager;
use App\Manager\AuthorManager;
use App\Manager\CategoryManager;
use App\Manager\CollectionManager;
use App\Manager\FeedManager;
use App\Manager\MemberManager;
use App\Model\ImportOpmlModel;
use App\Model\QueryParameterFilterModel;
use App\Model\QueryParameterPageModel;
use App\Model\QueryParameterSortModel;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api', name: 'api_feeds_', priority: 15)]
class FeedController extends AbstractAppController
{
    private AuthorManager $authorManager;
    private ActionManager $actionManager;
    private ActionFeedManager $actionFeedManager;
    private FeedManager $feedManager;
    private CollectionManager $collectionManager;
    private CategoryManager $categoryManager;
    private MemberManager $memberManager;

    public function __construct(AuthorManager $authorManager, ActionManager $actionManager, ActionFeedManager $actionFeedManager, FeedManager $feedManager, CollectionManager $collectionManager, CategoryManager $categoryManager, MemberManager $memberManager)
    {
        $this->authorManager = $authorManager;
        $this->actionManager = $actionManager;
        $this->actionFeedManager = $actionFeedManager;
        $this->feedManager = $feedManager;
        $this->collectionManager = $collectionManager;
        $this->categoryManager = $categoryManager;
        $this->memberManager = $memberManager;
    }

    /**
     * @param array<mixed> $filter
     * @param array<mixed> $page
     */
    #[Route(path: '/feeds', name: 'index', methods: ['GET'])]
    public function index(Request $request, #[MapQueryParameter] ?array $page, #[MapQueryParameter] ?array $filter, #[MapQueryParameter] ?string $sort): JsonResponse
    {
        $data = [];
        $included = [];

        $this->denyAccessUnlessGranted('LIST', 'feed');

        $filtersModel = new QueryParameterFilterModel($filter);

        $parameters = [];

        if ($filtersModel->get('link')) {
            $parameters['link'] = $filtersModel->get('link');
        }

        if ($filtersModel->getBool('witherrors')) {
            $parameters['witherrors'] = true;
        }

        if ($filtersModel->getBool('subscribed')) {
            $parameters['subscribed'] = true;
            $parameters['member'] = $this->getMember();
        }

        if ($filtersModel->getBool('unsubscribed')) {
            $parameters['unsubscribed'] = true;
            $parameters['member'] = $this->getMember();
        }

        if ($filtersModel->getInt('category')) {
            if ($category = $this->categoryManager->getOne(['id' => $filtersModel->getInt('category')])) {
                $parameters['category'] = $filtersModel->getInt('category');
                $included['category-'.$category->getId()] = $category->getJsonApiData();
            }
        }

        if ($filtersModel->getInt('author')) {
            if ($author = $this->authorManager->getOne(['id' => $filtersModel->getInt('author')])) {
                $parameters['author'] = $filtersModel->getInt('author');
                $included['author-'.$author->getId()] = $author->getJsonApiData();
            }
        }

        if ($filtersModel->getInt('days')) {
            $parameters['days'] = $filtersModel->getInt('days');
        }

        $sortModel = new QueryParameterSortModel($sort);

        if ($sortGet = $sortModel->get()) {
            $parameters['sortDirection'] = $sortGet['direction'];
            $parameters['sortField'] = $sortGet['field'];
        } else {
            $parameters['sortDirection'] = 'ASC';
            $parameters['sortField'] = 'fed.title';
        }

        $parameters['returnQueryBuilder'] = true;

        $pageModel = new QueryParameterPageModel($page);

        $pagination = $this->paginateAbstract($pageModel, $this->feedManager->getList($parameters));

        $data = array_merge($data, $this->jsonApi($request, $pagination, $sortModel, $filtersModel));

        $data['data'] = [];

        $ids = [];
        foreach ($pagination as $result) {
            $ids[] = $result['id'];
        }

        $results = $this->actionFeedManager->getList(['member' => $this->getMember(), 'feeds' => $ids])->getResult();
        $actions = [];
        foreach ($results as $actionFeed) {
            $included['action-'.$actionFeed->getAction()->getId()] = $actionFeed->getAction()->getJsonApiData();
            $actions[$actionFeed->getFeed()->getId()][] = $actionFeed->getAction()->getId();
        }

        $results = $this->categoryManager->feedCategoryManager->getList(['member' => $this->getMember(), 'feeds' => $ids])->getResult();
        $categories = [];
        foreach ($results as $feedCategory) {
            $included['category-'.$feedCategory->getCategory()->getId()] = $feedCategory->getCategory()->getJsonApiData();
            $categories[$feedCategory->getFeed()->getId()][] = $feedCategory->getCategory()->getId();
        }

        foreach ($pagination as $result) {
            $feed = $this->feedManager->getOne(['id' => $result['id']]);
            if ($feed) {
                $entry = $feed->getJsonApiData();

                if (true === isset($actions[$result['id']])) {
                    $entry['relationships']['actions'] = [
                        'data' => [],
                    ];
                    foreach ($actions[$result['id']] as $actionId) {
                        $entry['relationships']['actions']['data'][] = [
                            'id'=> strval($actionId),
                            'type' => 'action',
                        ];
                    }
                }

                if (true === isset($categories[$result['id']])) {
                    $entry['relationships']['categories'] = [
                        'data' => [],
                    ];
                    foreach ($categories[$result['id']] as $categoryId) {
                        $entry['relationships']['categories']['data'][] = [
                            'id'=> strval($categoryId),
                            'type' => 'category',
                        ];
                    }
                }

                $data['data'][] = $entry;
            }
        }

        if (0 < count($included)) {
            $data['included'] = array_values($included);
        }

        return $this->jsonResponse($data);
    }

    #[Route(path: '/feeds', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = [];
        $case = 'subscribe';

        $this->denyAccessUnlessGranted('CREATE', 'feed');

        $feed = new Feed();
        $form = $this->createForm(FeedType::class, $feed);

        $content = $this->getContent($request);
        $form->submit($content);

        if ($form->isValid()) {
            $this->feedManager->persist($form->getData());

            if ($feed->getId()) {
                $action = $this->actionManager->getOne(['title' => $case]);

                if ($action) {
                    $actionFeed = $this->actionFeedManager->getOne([
                        'action' => $action,
                        'feed' => $feed,
                        'member' => $this->getMember(),
                    ]);

                    $this->actionFeedManager->setAction($case, $action, $feed, $actionFeed, $this->getMember());
                }

                $this->collectionManager->start($feed->getId());
            }

            $data['data'] = $feed->getJsonApiData();
        } else {
            $data = $this->getFormErrors($form);
            return $this->jsonResponse($data, JsonResponse::HTTP_BAD_REQUEST);
        }

        return $this->jsonResponse($data, JsonResponse::HTTP_CREATED);
    }

    #[Route('/feed/{id}', name: 'read', methods: ['GET'])]
    public function read(Request $request, int $id): JsonResponse
    {
        $data = [];

        $feed = $this->feedManager->getOne(['id' => $id]);

        if (!$feed) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('READ', $feed);

        $data['data'] = [];
        $included = [];

        $entry = $feed->getJsonApiData();

        $results = $this->actionFeedManager->getList(['member' => $this->getMember(), 'feed' => $feed])->getResult();
        $actions = [];
        foreach ($results as $actionFeed) {
            $included['action-'.$actionFeed->getAction()->getId()] = $actionFeed->getAction()->getJsonApiData();
            $actions[$actionFeed->getFeed()->getId()][] = $actionFeed->getAction()->getId();
        }

        $categories = [];
        foreach ($this->categoryManager->feedCategoryManager->getList(['member' => $this->getMember(), 'feed' => $feed])->getResult() as $feedCategory) {
            $included['category-'.$feedCategory->getCategory()->getId()] = $feedCategory->getCategory()->getJsonApiData();
            $categories[$feedCategory->getFeed()->getId()][] = $feedCategory->getCategory()->getId();
        }

        if (true === isset($actions[$entry['id']])) {
            $entry['relationships']['actions'] = [
                'data' => [],
            ];
            foreach ($actions[$entry['id']] as $actionId) {
                $entry['relationships']['actions']['data'][] = [
                    'id'=> strval($actionId),
                    'type' => 'action',
                ];
            }
        }

        if (true === isset($categories[$entry['id']])) {
            $entry['relationships']['categories'] = [
                'data' => [],
            ];
            foreach ($categories[$entry['id']] as $categoryId) {
                $entry['relationships']['categories']['data'][] = [
                    'id'=> strval($categoryId),
                    'type' => 'category',
                ];
            }
        }

        $collections = [];
        foreach ($this->feedManager->collectionFeedManager->getList(['feed' => $feed, 'error_notnull' => true])->getResult() as $collectionFeed) {
            $included['collection_feed-'.$collectionFeed->getId()] = $collectionFeed->getJsonApiData();
            $collections[$collectionFeed->getFeed()->getId()][] = $collectionFeed->getId();
        }

        if (true === isset($collections[$feed->getId()])) {
            $entry['relationships']['collections'] = [
                'data' => [],
            ];
            foreach ($collections[$feed->getId()] as $collectionFeedId) {
                $entry['relationships']['collections']['data'][] = [
                    'id'=> strval($collectionFeedId),
                    'type' => 'collection_feed',
                ];
            }
        }

        $data['data'] = $entry;

        if (0 < count($included)) {
            $data['included'] = array_values($included);
        }

        return $this->jsonResponse($data);
    }

    #[Route('/feed/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $data = [];

        $feed = $this->feedManager->getOne(['id' => $id]);

        if (!$feed) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('UPDATE', $feed);

        $form = $this->createForm(FeedType::class, $feed);

        $content = $this->getContent($request);
        $form->submit($content);

        if ($form->isValid()) {
            $this->feedManager->persist($form->getData());

            $data['data'] = $feed->getJsonApiData();
        } else {
            $data = $this->getFormErrors($form);
            return $this->jsonResponse($data, JsonResponse::HTTP_BAD_REQUEST);
        }

        return $this->jsonResponse($data);
    }

    #[Route('/feed/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, int $id): JsonResponse
    {
        $data = [];

        $feed = $this->feedManager->getOne(['id' => $id]);

        if (!$feed) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('DELETE', $feed);

        $data['data'] = $feed->getJsonApiData();

        $this->feedManager->remove($feed);

        return $this->jsonResponse($data);
    }

    #[Route('/feed/action/subscribe/{id}', name: 'action_subscribe', methods: ['GET'])]
    public function actionSubscribe(Request $request, int $id): JsonResponse
    {
        $data = [];
        $case = 'subscribe';

        $feed = $this->feedManager->getOne(['id' => $id]);

        if (!$feed) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $action = $this->actionManager->getOne(['title' => $case]);

        if (!$action) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('ACTION_'.strtoupper($case), $feed);

        $actionFeed = $this->actionFeedManager->getOne([
            'action' => $action,
            'feed' => $feed,
            'member' => $this->getMember(),
        ]);

        $data = $this->actionFeedManager->setAction($case, $action, $feed, $actionFeed, $this->getMember());

        if ($this->getMember() && $this->getMember()->getId()) {
            $data['unread'] = $this->memberManager->countUnread($this->getMember()->getId());
        }

        return $this->jsonResponse($data);
    }

    #[Route(path: '/feeds/import', name: 'import', methods: ['POST'])]
    public function import(Request $request): JsonResponse
    {
        $data = [];

        $importOpml = new ImportOpmlModel();
        $form = $this->createForm(ImportOpmlType::class, $importOpml);

        $content = $this->getContent($request);
        $form->submit($content);

        if ($form->isValid() && $this->getMember()) {
            if ($file = $request->files->get('file')) {
                if ($file instanceof UploadedFile) {
                    $obj_simplexml = simplexml_load_file($file->getPathname());
                    if ($obj_simplexml) {
                        $this->feedManager->import($this->getMember(), $obj_simplexml->body);
                    }
                }
            }
        } else {
            $data = $this->getFormErrors($form);
            return $this->jsonResponse($data, JsonResponse::HTTP_BAD_REQUEST);
        }

        return $this->jsonResponse($data);
    }

    #[Route(path: '/feeds/export', name: 'export', methods: ['POST'])]
    public function export(Request $request): Response
    {
        $data = [];

        $parameters = [];
        if ('your_subscriptions' == $request->request->get('choice')) {
            $parameters['subscribed'] = true;
            $parameters['member'] = $this->getMember();
        }
        $parameters['sortField'] = 'fed.dateCreated';
        $parameters['sortDirection'] = 'ASC';

        $feeds = $this->feedManager->getList($parameters)->getResult();

        $xml = '<?xml version="1.0" encoding="UTF-8"?><opml version="2.0">';
        $xml .= "\r\n";
        $xml .= '<head>';
        $xml .= "\r\n";
        $xml .= '<title>Subscriptions from Reader Self</title>';
        $xml .= "\r\n";
        $xml .= '<docs>http://dev.opml.org/spec2.html</docs>';
        $xml .= "\r\n";
        if ($this->getMember()) {
            $xml .= '<ownerEmail>'.$this->getMember()->getEmail().'</ownerEmail>';
        }
        $xml .= "\r\n";
        $xml .= '</head>';
        $xml .= "\r\n";
        $xml .= '<body>';
        $xml .= "\r\n";

        foreach ($feeds as $row) {
            $feed = $this->feedManager->getOne(['id' => $row['id']]);

            if ($feed) {
                if ($title = $feed->getTitle()) {
                    $title = str_replace('&', '&amp;', $title);
                    $title = str_replace('""', '&quot;', $title);
                }

                if ($link = $feed->getLink()) {
                    $link = str_replace('&', '&amp;', $link);
                }

                if ($website = $feed->getWebsite()) {
                    $website = str_replace('&', '&amp;', $website);
                }

                $xml .= '<outline text="'.$title.'" title="'.$title.'" type="rss" xmlUrl="'.$link.'" htmlUrl="'.$website.'"/>';
                $xml .= "\r\n";
            }
        }
        $xml .= '</body>';
        $xml .= "\r\n";
        $xml .= '</opml>';
        $xml .= "\r\n";

        $response = new Response($xml);
        $response->headers->set('Content-Type', 'application/xml');
        return $response;
    }
}
