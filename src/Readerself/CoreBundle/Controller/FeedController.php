<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Readerself\CoreBundle\Controller\AbstractController;
use Readerself\CoreBundle\Manager\FeedManager;
use Readerself\CoreBundle\Manager\ActionManager;

use Readerself\CoreBundle\Form\Type\FeedType;

class FeedController extends AbstractController
{
    protected $feedManager;

    protected $actionManager;

    public function __construct(
        FeedManager $feedManager,
        ActionManager $actionManager
    ) {
        $this->feedManager = $feedManager;
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
     *     },
     * )
     */
    public function indexAction(Request $request)
    {
        $data = [];
        if(!$member = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $parameters = [];

        if($request->query->get('errors')) {
            $parameters['errors'] = true;
        }

        if($request->query->get('subscribed')) {
            $parameters['subscribed'] = true;
            $parameters['member'] = $member;
        }

        if($request->query->get('not_subscribed')) {
            $parameters['not_subscribed'] = true;
            $parameters['member'] = $member;
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
            $actions = $this->get('readerself_core_manager_action')->actionFeedMemberManager->getList(['member' => $member, 'feed' => $feed]);

            $data['entries'][$index] = $feed->toArray();
            foreach($actions as $action) {
                $data['entries'][$index][$action->getAction()->getTitle()] = true;
            }
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
        if(!$member = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $form = $this->createForm(FeedType::class, $this->feedManager->init());

        $form->submit($request->request->all(), false);

        if($form->isValid()) {
            $id = $this->feedManager->persist($form->getData());

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
        if(!$member = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $feed = $this->feedManager->getOne(['id' => $id]);
        $actions = $this->get('readerself_core_manager_action')->actionFeedMemberManager->getList(['member' => $member, 'feed' => $feed]);

        $collections = [];
        foreach($this->feedManager->collectionFeedManager->getList(['feed' => $feed]) as $collection) {
            $collections[] = $collection->toArray();
        }

        $data['entry'] = $feed->toArray();
        foreach($actions as $action) {
            $data['entry'][$action->getAction()->getTitle()] = true;
        }
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
        if(!$member = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $form = $this->createForm(FeedType::class, $this->feedManager->getOne(['id' => $id]));

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
        if(!$member = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $feed = $this->feedManager->getOne(['id' => $id]);

        $data['entry'] = $feed;
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
        if(!$member = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $action = $this->actionManager->getOne(['title' => $case]);
        $feed = $this->feedManager->getOne(['id' => $id]);

        if($actionFeedMember = $this->actionManager->actionFeedMemberManager->getOne([
            'action' => $action,
            'feed' => $feed,
            'member' => $member,
        ])) {
            $this->actionManager->actionFeedMemberManager->remove($actionFeedMember);
            $data['action'] = 'un'.$case;
            $data['action_reverse'] = $case;
        } else {
            $actionFeedMember = $this->actionManager->actionFeedMemberManager->init();
            $actionFeedMember->setAction($action);
            $actionFeedMember->setFeed($feed);
            $actionFeedMember->setMember($member);

            $this->actionManager->actionFeedMemberManager->persist($actionFeedMember);
            $data['action'] = $case;
            $data['action_reverse'] = 'un'.$case;
        }

        $data['entry'] = $feed->toArray();
        $data['entry_entity'] = 'feed';

        return new JsonResponse($data);
    }
}
