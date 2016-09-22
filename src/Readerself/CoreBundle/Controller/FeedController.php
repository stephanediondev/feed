<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Readerself\CoreBundle\Controller\AbstractController;

use Readerself\CoreBundle\Manager\FeedManager;
use Readerself\CoreBundle\Manager\CategoryManager;
use Readerself\CoreBundle\Manager\ActionManager;

use Readerself\CoreBundle\Form\Type\FeedType;

class FeedController extends AbstractController
{
    protected $feedManager;

    protected $categoryManager;

    protected $actionManager;

    public function __construct(
        FeedManager $feedManager,
        CategoryManager $categoryManager,
        ActionManager $actionManager
    ) {
        $this->feedManager = $feedManager;
        $this->categoryManager = $categoryManager;
        $this->actionManager = $actionManager;
    }

    /**
     * Retrieve all feeds.
     *
     * @ApiDoc(
     *     section="_ Feed",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     *     parameters={
     *         {"name"="sortDirection", "dataType"="string", "required"=false, "format"="""asc"" or ""desc"", default ""desc""", "description"=""},
     *         {"name"="page", "dataType"="integer", "required"=false, "format"="default ""1""", "description"="page number"},
     *         {"name"="perPage", "dataType"="integer", "required"=false, "format"="default ""100""", "description"="items per page"},
     *         {"name"="subscribed", "dataType"="integer", "required"=false, "format"="1 or 0", "description"="subscribed feeds"},
     *         {"name"="unsubscribed", "dataType"="integer", "required"=false, "format"="1 or 0", "description"="unsubscribed feeds"},
     *         {"name"="errors", "dataType"="integer", "required"=false, "format"="1 or 0", "description"="feeds with errors"},
     *         {"name"="category", "dataType"="integer", "required"=false, "format"="category ID", "description"="feeds by category"},
     *     },
     * )
     */
    public function indexAction(Request $request)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $parameters = [];

        if($request->query->get('errors')) {
            $parameters['errors'] = true;
        }

        if($request->query->get('subscribed')) {
            $parameters['subscribed'] = true;
            $parameters['member'] = $memberConnected;
        }

        if($request->query->get('not_subscribed')) {
            $parameters['not_subscribed'] = true;
            $parameters['member'] = $memberConnected;
        }

        if($request->query->get('category')) {
            $parameters['category'] = (int) $request->query->get('category');
            $data['entry'] = $this->categoryManager->getOne(['id' => (int) $request->query->get('category')])->toArray();
            $data['entry_entity'] = 'category';
        }

        $paginator= $this->get('knp_paginator');
        $paginator->setDefaultPaginatorOptions(['widgetParameterName' => 'page']);
        $pagination = $paginator->paginate(
            $this->feedManager->getList($parameters),
            $page = $request->query->getInt('page', 1),
            $request->query->getInt('perPage', 100)
        );

        $data['entries'] = [];
        $index = 0;
        foreach($pagination as $result) {
            $feed = $this->feedManager->getOne(['id' => $result['id']]);
            $actions = $this->get('readerself_core_manager_action')->actionFeedMemberManager->getList(['member' => $memberConnected, 'feed' => $feed]);

            $categories = [];
            foreach($this->categoryManager->feedCategoryManager->getList(['member' => $memberConnected, 'feed' => $feed]) as $feedCategory) {
                $categories[] = $feedCategory->toArray();
            }

            $data['entries'][$index] = $feed->toArray();
            foreach($actions as $action) {
                $data['entries'][$index][$action->getAction()->getTitle()] = true;
            }
            $data['entries'][$index]['categories'] = $categories;
            $index++;
        }
        $data['entries_entity'] = 'feed';
        $data['entries_total'] = $pagination->getTotalItemCount();
        $data['entries_pages'] = $pages = $pagination->getPageCount();
        $data['entries_page_current'] = $page;
        $pagePrevious = $page - 1;
        if($pagePrevious >= 1) {
            $data['entries_page_previous'] = $pagePrevious;
        }
        $pageNext = $page + 1;
        if($pageNext <= $pages) {
            $data['entries_page_next'] = $pageNext;
        }
        return new JsonResponse($data);
    }

    /**
     * Create a feed.
     *
     * @ApiDoc(
     *     section="Feed",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"="true"},
     *     },
     *     parameters={
     *         {"name"="title", "dataType"="string", "required"=false},
     *         {"name"="link", "dataType"="string", "required"=true},
     *         {"name"="website", "dataType"="string", "required"=false},
     *     },
     * )
     */
    public function createAction(Request $request)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $form = $this->createForm(FeedType::class, $this->feedManager->init());

        $form->submit($request->request->all(), false);

        if($form->isValid()) {
            $feed_id = $this->feedManager->persist($form->getData());

            $data['entry'] = $this->feedManager->getOne(['id' => $feed_id])->toArray();
            $data['entry_entity'] = 'feed';

        } else {
            $errors = $form->getErrors(true);
            foreach($errors as $error) {
                $data['errors'][$error->getOrigin()->getName()] = $error->getMessage();
            }
        }

        return new JsonResponse($data);
    }

    /**
     * Retrieve a feed.
     *
     * @ApiDoc(
     *     section="Feed",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function readAction(Request $request, $id)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $feed = $this->feedManager->getOne(['id' => $id]);

        if(!$feed) {
            return new JsonResponse($data, 404);
        }

        $actions = $this->get('readerself_core_manager_action')->actionFeedMemberManager->getList(['member' => $member, 'feed' => $feed]);

        $categories = [];
        foreach($this->categoryManager->feedCategoryManager->getList(['member' => $member, 'feed' => $feed]) as $feedCategory) {
            $categories[] = $feedCategory->toArray();
        }

        $collections = [];
        foreach($this->feedManager->collectionFeedManager->getList(['feed' => $feed]) as $collection) {
            $collections[] = $collection->toArray();
        }

        $data['entry'] = $feed->toArray();
        foreach($actions as $action) {
            $data['entry'][$action->getAction()->getTitle()] = true;
        }
        $data['entry']['categories'] = $categories;
        $data['entry']['collections'] = $collections;
        $data['entry_entity'] = 'feed';

        return new JsonResponse($data);
    }

    /**
     * Update a feed.
     *
     * @ApiDoc(
     *     section="Feed",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"="true"},
     *     },
     *     parameters={
     *         {"name"="title", "dataType"="string", "required"=false},
     *         {"name"="link", "dataType"="string", "required"=true},
     *         {"name"="website", "dataType"="string", "required"=false},
     *     },
     * )
     */
    public function updateAction(Request $request, $id)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $feed = $this->feedManager->getOne(['id' => $id]);

        if(!$feed) {
            return new JsonResponse($data, 404);
        }

        $form = $this->createForm(FeedType::class, $feed);

        $form->submit($request->request->all(), false);

        if($form->isValid()) {
            $this->feedManager->persist($form->getData());

            $data['entry'] = $this->feedManager->getOne(['id' => $id])->toArray();
            $data['entry_entity'] = 'feed';

        } else {
            $errors = $form->getErrors(true);
            foreach($errors as $error) {
                $data['errors'][$error->getOrigin()->getName()] = $error->getMessage();
            }
        }

        return new JsonResponse($data);
    }

    /**
     * Delete a feed.
     *
     * @ApiDoc(
     *     section="Feed",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function deleteAction(Request $request, $id)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $feed = $this->feedManager->getOne(['id' => $id]);

        if(!$feed) {
            return new JsonResponse($data, 404);
        }

        $data['entry'] = $feed->toArray();
        $data['entry_entity'] = 'feed';

        $this->feedManager->remove($feed);

        return new JsonResponse($data);
    }

    /**
     * Set "subscribe" action / Remove "subscribe" action.
     *
     * @ApiDoc(
     *     section="_ Feed",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function subscribeAction(Request $request, $id)
    {
        return $this->setAction('subscribe', $request, $id);
    }

    private function setAction($case, Request $request, $id)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $feed = $this->feedManager->getOne(['id' => $id]);

        if(!$feed) {
            return new JsonResponse($data, 404);
        }

        $action = $this->actionManager->getOne(['title' => $case]);

        if($actionFeedMember = $this->actionManager->actionFeedMemberManager->getOne([
            'action' => $action,
            'feed' => $feed,
            'member' => $memberConnected,
        ])) {
            $this->actionManager->actionFeedMemberManager->remove($actionFeedMember);
            $data['action'] = 'un'.$case;
            $data['action_reverse'] = $case;
        } else {
            $actionFeedMember = $this->actionManager->actionFeedMemberManager->init();
            $actionFeedMember->setAction($action);
            $actionFeedMember->setFeed($feed);
            $actionFeedMember->setMember($memberConnected);

            $this->actionManager->actionFeedMemberManager->persist($actionFeedMember);
            $data['action'] = $case;
            $data['action_reverse'] = 'un'.$case;
        }

        $data['entry'] = $feed->toArray();
        $data['entry_entity'] = 'feed';

        return new JsonResponse($data);
    }

    /**
     * Discover feeds.
     *
     * @ApiDoc(
     *     section="_ Feed",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function discoverAction(Request $request, $type)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $data['entries'] = [];

        if($type == 'digg') {
            $content = json_decode(file_get_contents('https://digg.com/api/discovery/list.json'), true);
            $index = 0;
            foreach($content['data'] as $category => $sub) {
                foreach($sub['subs'] as $result) {
                    $result['category'] = $category;
                    $this->feedManager->directCreate($result);

                    /*$data['entries'][$index] = [];
                    $data['entries'][$index]['discover'] = true;
                    $data['entries'][$index]['title'] = $result['title'];
                    $data['entries'][$index]['link'] = $result['feed_url'];
                    $data['entries'][$index]['website'] = $result['html_url'];
                    $data['entries'][$index]['description'] = $result['description'];
                    if(isset($parse_url['host']) == 1) {
                        $data['entries'][$index]['hostname'] = $parse_url['host'];
                    }
                    $data['entries'][$index]['categories'] = [];
                    $data['entries'][$index]['categories'][] = ['title' => mb_strtolower($category, 'UTF-8')];*/
                    $index++;
                }
            }
        }

        return new JsonResponse($data);
    }
}
