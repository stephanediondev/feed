<?php

namespace App\Controller;

use App\Manager\AuthorManager;
use App\Manager\ActionManager;
use App\Manager\FeedManager;
use App\Manager\CollectionManager;
use App\Manager\CategoryManager;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Controller\AbstractAppController;

use App\Form\Type\FeedType;

use App\Model\ImportOpmlModel;
use App\Form\Type\ImportOpmlType;

#[Route(path: '/api', name: 'api_feeds_')]
class FeedController extends AbstractAppController
{
    private AuthorManager $authorManager;
    private ActionManager $actionManager;
    private FeedManager $feedManager;
    private CollectionManager $collectionManager;
    private CategoryManager $categoryManager;

    public function __construct(AuthorManager $authorManager, ActionManager $actionManager, FeedManager $feedManager, CollectionManager $collectionManager, CategoryManager $categoryManager)
    {
        $this->authorManager = $authorManager;
        $this->actionManager = $actionManager;
        $this->feedManager = $feedManager;
        $this->collectionManager = $collectionManager;
        $this->categoryManager = $categoryManager;
    }

    #[Route(path: '/feeds', name: 'index', methods: ['GET'])]
    public function index(Request $request)
    {
        $data = [];
        $memberConnected = $this->validateToken($request);

        $parameters = [];

        if ($request->query->get('witherrors')) {
            if (!$memberConnected) {
                return new JsonResponse($data, 403);
            }
            $parameters['witherrors'] = true;
        }

        if ($request->query->get('subscribed')) {
            if (!$memberConnected) {
                return new JsonResponse($data, 403);
            }
            $parameters['subscribed'] = true;
            $parameters['member'] = $memberConnected;
        }

        if ($request->query->get('unsubscribed')) {
            if (!$memberConnected) {
                return new JsonResponse($data, 403);
            }
            $parameters['unsubscribed'] = true;
            $parameters['member'] = $memberConnected;
        }

        if ($request->query->get('category')) {
            $parameters['category'] = (int) $request->query->get('category');
            $data['entry'] = $this->categoryManager->getOne(['id' => (int) $request->query->get('category')])->toArray();
            $data['entry_entity'] = 'category';
        }

        if ($request->query->get('author')) {
            $parameters['author'] = (int) $request->query->get('author');
            $data['entry'] = $this->authorManager->getOne(['id' => (int) $request->query->get('author')])->toArray();
            $data['entry_entity'] = 'author';
        }

        if ($request->query->get('days')) {
            $parameters['days'] = (int) $request->query->get('days');
        }

        $fields = ['title' => 'fed.title', 'date_created' => 'fed.dateCreated'];
        if ($request->query->get('sortField') && array_key_exists($request->query->get('sortField'), $fields)) {
            $parameters['sortField'] = $fields[$request->query->get('sortField')];
        } else {
            $parameters['sortField'] = 'fed.title';
        }

        $directions = ['ASC', 'DESC'];
        if ($request->query->get('sortDirection') && in_array($request->query->get('sortDirection'), $directions)) {
            $parameters['sortDirection'] = $request->query->get('sortDirection');
        } else {
            $parameters['sortDirection'] = 'ASC';
        }

        $parameters['returnQueryBuilder'] = true;

        $pagination = $this->paginateAbstract($this->feedManager->getList($parameters), $page = intval($request->query->getInt('page', 1)), intval($request->query->getInt('perPage', 20)));

        $data['entries_entity'] = 'feed';
        $data['entries_total'] = $pagination->getTotalItemCount();
        $data['entries_pages'] = $pages = ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage());
        $data['entries_page_current'] = $page;
        $pagePrevious = $page - 1;
        if ($pagePrevious >= 1) {
            $data['entries_page_previous'] = $pagePrevious;
        }
        $pageNext = $page + 1;
        if ($pageNext <= $pages) {
            $data['entries_page_next'] = $pageNext;
        }

        $data['entries'] = [];

        $index = 0;
        foreach ($pagination as $result) {
            $feed = $this->feedManager->getOne(['id' => $result['id']]);
            $actions = $this->actionManager->actionFeedManager->getList(['member' => $memberConnected, 'feed' => $feed])->getResult();

            $categories = [];
            foreach ($this->categoryManager->feedCategoryManager->getList(['member' => $memberConnected, 'feed' => $feed])->getResult() as $feedCategory) {
                $categories[] = $feedCategory->toArray();
            }

            $data['entries'][$index] = $feed->toArray();
            foreach ($actions as $action) {
                $data['entries'][$index][$action->getAction()->getTitle()] = true;
            }
            $data['entries'][$index]['categories'] = $categories;
            $index++;
        }

        return new JsonResponse($data);
    }

    public function create(Request $request)
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $form = $this->createForm(FeedType::class, $this->feedManager->init());

        $form->submit($request->request->all(), false);

        if ($form->isValid()) {
            $test = $this->feedManager->getOne(['link' => $form->getData()->getLink()]);

            if (!$test) {
                $feed_id = $this->feedManager->persist($form->getData());
                $resutlAction = $this->setAction('subscribe', $request, $feed_id);

                $this->collectionManager->start($feed_id);

                return $resutlAction;
            } else {
                $data['entry'] = $test->toArray();
                $data['entry_entity'] = 'feed';
            }
        } else {
            $errors = $form->getErrors(true);
            foreach ($errors as $error) {
                $data['errors'][$error->getOrigin()->getName()] = $error->getMessage();
            }
        }

        return new JsonResponse($data);
    }

    #[Route('/feed/{id}', name: 'read', methods: ['GET'])]
    public function read(Request $request, $id)
    {
        $data = [];
        $memberConnected = $this->validateToken($request);

        $feed = $this->feedManager->getOne(['id' => $id]);

        if (!$feed) {
            return new JsonResponse($data, 404);
        }

        $actions = $this->actionManager->actionFeedManager->getList(['member' => $memberConnected, 'feed' => $feed])->getResult();

        $categories = [];
        foreach ($this->categoryManager->feedCategoryManager->getList(['member' => $memberConnected, 'feed' => $feed])->getResult() as $feedCategory) {
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

        return new JsonResponse($data);
    }

    #[Route('/feed/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, $id)
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $feed = $this->feedManager->getOne(['id' => $id]);

        if (!$feed) {
            return new JsonResponse($data, 404);
        }

        $form = $this->createForm(FeedType::class, $feed);

        $form->submit($request->request->all(), false);

        if ($form->isValid()) {
            $this->feedManager->persist($form->getData());

            $data['entry'] = $this->feedManager->getOne(['id' => $id])->toArray();
            $data['entry_entity'] = 'feed';
        } else {
            $errors = $form->getErrors(true);
            foreach ($errors as $error) {
                $data['errors'][$error->getOrigin()->getName()] = $error->getMessage();
            }
        }

        return new JsonResponse($data);
    }

    #[Route('/feed/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, $id)
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        if (!$memberConnected->getAdministrator()) {
            return new JsonResponse($data, 403);
        }

        $feed = $this->feedManager->getOne(['id' => $id]);

        if (!$feed) {
            return new JsonResponse($data, 404);
        }

        $data['entry'] = $feed->toArray();
        $data['entry_entity'] = 'feed';

        $this->feedManager->remove($feed);

        return new JsonResponse($data);
    }

    #[Route('/feed/action/subscribe/{id}', name: 'subscribe', methods: ['GET'])]
    public function actionSubscribe(Request $request, $id)
    {
        return $this->setAction('subscribe', $request, $id);
    }

    private function setAction($case, Request $request, $id)
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $feed = $this->feedManager->getOne(['id' => $id]);

        if (!$feed) {
            return new JsonResponse($data, 404);
        }

        $action = $this->actionManager->getOne(['title' => $case]);

        if ($actionFeed = $this->actionManager->actionFeedManager->getOne([
            'action' => $action,
            'feed' => $feed,
            'member' => $memberConnected,
        ])) {
            $this->actionManager->actionFeedManager->remove($actionFeed);

            $data['action'] = $action->getReverse()->getTitle();
            $data['action_reverse'] = $action->getTitle();

            if ($action->getReverse()) {
                if ($actionFeedReverse = $this->actionManager->actionFeedManager->getOne([
                    'action' => $action->getReverse(),
                    'feed' => $feed,
                    'member' => $memberConnected,
                ])) {
                } else {
                    $actionFeedReverse = $this->actionManager->actionFeedManager->init();
                    $actionFeedReverse->setAction($action->getReverse());
                    $actionFeedReverse->setFeed($feed);
                    $actionFeedReverse->setMember($memberConnected);
                    $this->actionManager->actionFeedManager->persist($actionFeedReverse);
                }
            }
        } else {
            $actionFeed = $this->actionManager->actionFeedManager->init();
            $actionFeed->setAction($action);
            $actionFeed->setFeed($feed);
            $actionFeed->setMember($memberConnected);
            $this->actionManager->actionFeedManager->persist($actionFeed);

            $data['action'] = $action->getTitle();
            $data['action_reverse'] = $action->getReverse()->getTitle();

            if ($action->getReverse()) {
                if ($actionFeedReverse = $this->actionManager->actionFeedManager->getOne([
                    'action' => $action->getReverse(),
                    'feed' => $feed,
                    'member' => $memberConnected,
                ])) {
                    $this->actionManager->actionFeedManager->remove($actionFeedReverse);
                }
            }
        }

        $data['entry'] = $feed->toArray();
        $data['entry_entity'] = 'feed';

        if ($case == 'subscribe') {
            $data['unread'] = $this->memberManager->countUnread($memberConnected->getId());
        }

        return new JsonResponse($data);
    }

    #[Route(path: '/feeds/import', name: 'import', methods: ['POST'])]
    public function import(Request $request)
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $importOpml = new ImportOpmlModel();
        $form = $this->createForm(ImportOpmlType::class, $importOpml);

        $form->submit($request->request->all(), false);

        if ($form->isValid()) {
            $obj_simplexml = simplexml_load_file($request->files->get('file'));
            if ($obj_simplexml) {
                $this->feedManager->import($memberConnected, $obj_simplexml->body);
            }
        } else {
            $errors = $form->getErrors(true);
            foreach ($errors as $error) {
                $data['errors'][$error->getOrigin()->getName()] = $error->getMessage();
            }
        }

        return new JsonResponse($data);
    }

    #[Route(path: '/feeds/export', name: 'export', methods: ['POST'])]
    public function export(Request $request)
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $parameters = [];
        if ('your_subscriptions' == $request->request->get('choice')) {
            $parameters['subscribed'] = true;
            $parameters['member'] = $memberConnected;
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
        $xml .= '<ownerEmail>'.$memberConnected->getEmail().'</ownerEmail>';
        $xml .= "\r\n";
        $xml .= '</head>';
        $xml .= "\r\n";
        $xml .= '<body>';
        $xml .= "\r\n";

        foreach ($feeds as $feed) {
            $feed = $this->feedManager->getOne(['id' => $feed['id']]);

            $title = $feed->getTitle();
            $title = str_replace('&', '&amp;', $title);
            $title = str_replace('""', '&quot;', $title);

            $link = $feed->getLink();
            $link = str_replace('&', '&amp;', $link);

            $website = $feed->getWebsite();
            $website = str_replace('&', '&amp;', $website);

            $xml .= '<outline text="'.$title.'" title="'.$title.'" type="rss" xmlUrl="'.$link.'" htmlUrl="'.$website.'"/>';
            $xml .= "\r\n";
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
