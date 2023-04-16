<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Entity\ActionFeed;
use App\Entity\Feed;
use App\Entity\Member;
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
use App\Model\QueryParameterSortModel;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

    #[Route(path: '/feeds', name: 'index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $data = [];

        $this->denyAccessUnlessGranted('LIST', 'feed');

        $filters = new QueryParameterFilterModel($request->query->all('filter'));

        $parameters = [];

        if ($filters->getBool('witherrors')) {
            $parameters['witherrors'] = true;
        }

        if ($filters->getBool('subscribed')) {
            $parameters['subscribed'] = true;
            $parameters['member'] = $this->getMember();
        }

        if ($filters->getBool('unsubscribed')) {
            $parameters['unsubscribed'] = true;
            $parameters['member'] = $this->getMember();
        }

        if ($filters->getInt('category')) {
            if ($category = $this->categoryManager->getOne(['id' => $filters->getInt('category')])) {
                $parameters['category'] = $filters->getInt('category');
                $data['entry'] = $category->toArray();
                $data['entry_entity'] = 'category';
            }
        }

        if ($filters->getInt('author')) {
            if ($author = $this->authorManager->getOne(['id' => $filters->getInt('author')])) {
                $parameters['author'] = $filters->getInt('author');
                $data['entry'] = $author->toArray();
                $data['entry_entity'] = 'author';
            }
        }

        if ($filters->getInt('days')) {
            $parameters['days'] = $filters->getInt('days');
        }

        $sort = (new QueryParameterSortModel($request->query->get('sort')))->get();

        if ($sort) {
            $parameters['sortDirection'] = $sort['direction'];
            $parameters['sortField'] = $sort['field'];
        } else {
            $parameters['sortDirection'] = 'ASC';
            $parameters['sortField'] = 'fed.title';
        }

        $parameters['returnQueryBuilder'] = true;

        $pagination = $this->paginateAbstract($this->feedManager->getList($parameters));

        $data['entries_entity'] = 'feed';
        $data['entries_total'] = $pagination->getTotalItemCount();
        $data['entries_pages'] = $pages = ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage());
        $data['entries_page_current'] = $pagination->getCurrentPageNumber();
        $pagePrevious = $pagination->getCurrentPageNumber() - 1;
        if ($pagePrevious >= 1) {
            $data['entries_page_previous'] = $pagePrevious;
        }
        $pageNext = $pagination->getCurrentPageNumber() + 1;
        if ($pageNext <= $pages) {
            $data['entries_page_next'] = $pageNext;
        }

        $data['entries'] = [];

        $ids = [];
        foreach ($pagination as $result) {
            $ids[] = $result['id'];
        }

        $results = $this->actionFeedManager->getList(['member' => $this->getMember(), 'feeds' => $ids])->getResult();
        $actions = [];
        foreach ($results as $actionFeed) {
            $actions[$actionFeed->getFeed()->getId()][] = $actionFeed;
        }

        $results = $this->categoryManager->feedCategoryManager->getList(['member' => $this->getMember(), 'feeds' => $ids])->getResult();
        $categories = [];
        foreach ($results as $feedCategory) {
            $categories[$feedCategory->getFeed()->getId()][] = $feedCategory->toArray();
        }

        foreach ($pagination as $result) {
            $feed = $this->feedManager->getOne(['id' => $result['id']]);
            if ($feed) {
                $entry = $feed->toArray();

                if (true === isset($actions[$result['id']])) {
                    foreach ($actions[$result['id']] as $action) {
                        $entry[$action->getAction()->getTitle()] = true;
                    }
                }

                if (true === isset($categories[$result['id']])) {
                    $entry['categories'] = $categories[$result['id']];
                }

                $data['entries'][] = $entry;
            }
        }

        return $this->jsonResponse($data);
    }

    #[Route(path: '/feeds', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = [];

        $this->denyAccessUnlessGranted('CREATE', 'feed');

        $feed = new Feed();
        $form = $this->createForm(FeedType::class, $feed);

        $content = $this->getContent($request);
        $form->submit($content);

        if ($form->isValid()) {
            $this->feedManager->persist($form->getData());

            if ($feed->getId()) {
                $this->setAction('subscribe', $request, $feed->getId());
                $this->collectionManager->start($feed->getId());
            }

            $data['entry'] = $feed->toArray();
            $data['entry_entity'] = 'feed';
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

        $actions = $this->actionFeedManager->getList(['member' => $this->getMember(), 'feed' => $feed])->getResult();

        $categories = [];
        foreach ($this->categoryManager->feedCategoryManager->getList(['member' => $this->getMember(), 'feed' => $feed])->getResult() as $feedCategory) {
            $categories[] = $feedCategory->toArray();
        }

        $collections = [];
        foreach ($this->feedManager->collectionFeedManager->getList(['feed' => $feed])->getResult() as $collection) {
            $collections[] = $collection->toArray();
        }

        $data['entry'] = $feed->toArray();
        foreach ($actions as $action) {
            $data['entry'][$action->getAction()->getTitle()] = true;
        }
        $data['entry']['categories'] = $categories;
        $data['entry']['collections'] = $collections;
        $data['entry_entity'] = 'feed';

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

            $data['entry'] = $feed->toArray();
            $data['entry_entity'] = 'feed';
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

        $data['entry'] = $feed->toArray();
        $data['entry_entity'] = 'feed';

        $this->feedManager->remove($feed);

        return $this->jsonResponse($data);
    }

    #[Route('/feed/action/subscribe/{id}', name: 'action_subscribe', methods: ['GET'])]
    public function actionSubscribe(Request $request, int $id): JsonResponse
    {
        return $this->setAction('subscribe', $request, $id);
    }

    private function setAction(string $case, Request $request, int $id): JsonResponse
    {
        $data = [];

        $feed = $this->feedManager->getOne(['id' => $id]);

        if (!$feed) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $action = $this->actionManager->getOne(['title' => $case]);

        if (!$action) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('ACTION_'.strtoupper($case), $feed);

        if ($actionFeed = $this->actionFeedManager->getOne([
            'action' => $action,
            'feed' => $feed,
            'member' => $this->getMember(),
        ])) {
            $this->actionFeedManager->remove($actionFeed);

            $data['action'] = $action->getReverse() ? $action->getReverse()->getTitle() : null;
            $data['action_reverse'] = $action->getTitle();

            if ($action->getReverse()) {
                if ($actionFeedReverse = $this->actionFeedManager->getOne([
                    'action' => $action->getReverse(),
                    'feed' => $feed,
                    'member' => $this->getMember(),
                ])) {
                } else {
                    $actionFeedReverse = new ActionFeed();
                    $actionFeedReverse->setAction($action->getReverse());
                    $actionFeedReverse->setFeed($feed);
                    $actionFeedReverse->setMember($this->getMember());
                    $this->actionFeedManager->persist($actionFeedReverse);
                }
            }
        } else {
            $actionFeed = new ActionFeed();
            $actionFeed->setAction($action);
            $actionFeed->setFeed($feed);
            $actionFeed->setMember($this->getMember());
            $this->actionFeedManager->persist($actionFeed);

            $data['action'] = $action->getTitle();
            $data['action_reverse'] = $action->getReverse() ? $action->getReverse()->getTitle() : null;

            if ($action->getReverse()) {
                if ($actionFeedReverse = $this->actionFeedManager->getOne([
                    'action' => $action->getReverse(),
                    'feed' => $feed,
                    'member' => $this->getMember(),
                ])) {
                    $this->actionFeedManager->remove($actionFeedReverse);
                }
            }
        }

        $data['entry'] = $feed->toArray();
        $data['entry_entity'] = 'feed';

        if ($case == 'subscribe' && $this->getMember() && $this->getMember()->getId()) {
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
