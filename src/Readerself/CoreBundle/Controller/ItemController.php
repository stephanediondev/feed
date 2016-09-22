<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Readerself\CoreBundle\Controller\AbstractController;

use Readerself\CoreBundle\Manager\ItemManager;
use Readerself\CoreBundle\Manager\CategoryManager;
use Readerself\CoreBundle\Manager\ActionManager;

class ItemController extends AbstractController
{
    protected $itemManager;

    protected $categoryManager;

    protected $actionManager;

    public function __construct(
        ItemManager $itemManager,
        CategoryManager $categoryManager,
        ActionManager $actionManager
    ) {
        $this->itemManager = $itemManager;
        $this->categoryManager = $categoryManager;
        $this->actionManager = $actionManager;
    }

    /**
     * Retrieve all items.
     *
     * @ApiDoc(
     *     section="_ Item",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     *     parameters={
     *         {"name"="sortDirection", "dataType"="string", "required"=false, "format"="""asc"" or ""desc"", default ""desc""", "description"=""},
     *         {"name"="page", "dataType"="integer", "required"=false, "format"="default ""1""", "description"="page number"},
     *         {"name"="perPage", "dataType"="integer", "required"=false, "format"="default ""20""", "description"="items per page"},
     *         {"name"="starred", "dataType"="integer", "required"=false, "format"="1 or 0", "description"="items with action ""star"""},
     *         {"name"="unread", "dataType"="integer", "required"=false, "format"="1 or 0", "description"="items with no action ""read"""},
     *         {"name"="geolocation", "dataType"="integer", "required"=false, "format"="1 or 0", "description"="items with geolocation"},
     *         {"name"="feed", "dataType"="integer", "required"=false, "format"="feed ID", "description"="items by feed"},
     *         {"name"="author", "dataType"="integer", "required"=false, "format"="author ID", "description"="items by author"},
     *         {"name"="category", "dataType"="integer", "required"=false, "format"="category ID", "description"="items by category"},
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         403="Returned when the user is not authorized to say hello",
     *         404={
     *           "Returned when the user is not found",
     *           "Returned when something else is not found"
     *         }
     *     }
     * )
     */
    public function indexAction(Request $request)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $parameters = [];
        $parameters['member'] = $memberConnected;

        if($request->query->get('order')) {
            $parameters['order'] = (string) $request->query->get('order');
        }

        if($request->query->get('starred')) {
            $parameters['starred'] = $request->query->get('starred');
        }

        if($request->query->get('unread')) {
            $parameters['unread'] = (bool) $request->query->get('unread');
        }

        if($request->query->get('geolocation')) {
            $parameters['geolocation'] = (bool) $request->query->get('geolocation');
        }

        if($request->query->get('feed')) {
            $parameters['feed'] = (int) $request->query->get('feed');
            $data['entry'] = $this->get('readerself_core_manager_feed')->getOne(['id' => (int) $request->query->get('feed')])->toArray();
            $data['entry_entity'] = 'feed';
        }

        if($request->query->get('author')) {
            $parameters['author'] = (int) $request->query->get('author');
            $data['entry'] = $this->get('readerself_core_manager_author')->getOne(['id' => (int) $request->query->get('author')])->toArray();
            $data['entry_entity'] = 'author';
        }

        if($request->query->get('category')) {
            $parameters['category'] = (int) $request->query->get('category');
            $data['entry'] = $this->categoryManager->getOne(['id' => (int) $request->query->get('category')])->toArray();
            $data['entry_entity'] = 'category';
        }

        if($request->query->get('unread')) {
            $page = 1;
        } else {
            $page = $request->query->getInt('page', 1);
        }

        $paginator= $this->get('knp_paginator');
        $paginator->setDefaultPaginatorOptions(['widgetParameterName' => 'page']);
        $pagination = $paginator->paginate(
            $this->itemManager->getList($parameters),
            $page,
            $request->query->getInt('perPage', 20)
        );

        $data['entries'] = [];
        $index = 0;
        foreach($pagination as $result) {
            $item = $this->itemManager->getOne(['id' => $result['id']]);

            $actions = $this->actionManager->actionItemMemberManager->getList(['member' => $memberConnected, 'item' => $item]);

            $categories = [];
            foreach($this->categoryManager->itemCategoryManager->getList(['member' => $memberConnected, 'item' => $item]) as $itemCategory) {
                $categories[] = $itemCategory->toArray();
            }

            $enclosures = [];
            foreach($this->itemManager->enclosureManager->getList(['item' => $item]) as $enclosure) {
                $enclosures[] = $enclosure->toArray();
            }

            $data['entries'][$index] = $item->toArray();
            foreach($actions as $action) {
                $data['entries'][$index][$action->getAction()->getTitle()] = true;
            }
            $data['entries'][$index]['categories'] = $categories;
            $data['entries'][$index]['enclosures'] = $enclosures;
            $index++;
        }
        $data['entries_entity'] = 'item';
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
     * Mark all items as read.
     *
     * @ApiDoc(
     *     section="_ Item",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     *     parameters={
     *         {"name"="starred", "dataType"="integer", "required"=false, "format"="1 or 0", "description"="items with action ""star"""},
     *         {"name"="feed", "dataType"="integer", "required"=false, "format"="feed ID", "description"="items by feed"},
     *         {"name"="author", "dataType"="integer", "required"=false, "format"="author ID", "description"="items by author"},
     *         {"name"="category", "dataType"="integer", "required"=false, "format"="category ID", "description"="items by category"},
     *     },
     * )
     */
    public function markallasreadAction(Request $request)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $parameters = [];
        $parameters['member'] = $memberConnected;

        $parameters['unread'] = (bool) $request->query->get('unread');

        if($request->query->get('starred')) {
            $parameters['starred'] = $request->query->get('starred');
        }

        if($request->query->get('feed')) {
            $parameters['feed'] = (int) $request->query->get('feed');
        }

        if($request->query->get('author')) {
            $parameters['author'] = (int) $request->query->get('author');
        }

        if($request->query->get('category')) {
            $parameters['category'] = (int) $request->query->get('category');
        }

        $this->itemManager->readAll($parameters);

        return new JsonResponse($data);
    }

    /**
     * Set "read" action / Remove "read" action.
     *
     * @ApiDoc(
     *     section="_ Item",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function readAction(Request $request, $id)
    {
        return $this->setAction('read', $request, $id);
    }

    /**
     * Set "star" action / Remove "star" action.
     *
     * @ApiDoc(
     *     section="_ Item",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function starAction(Request $request, $id)
    {
        return $this->setAction('star', $request, $id);
    }

    private function setAction($case, Request $request, $id)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $item = $this->itemManager->getOne(['id' => $id]);

        if(!$item) {
            return new JsonResponse($data, 404);
        }

        $action = $this->actionManager->getOne(['title' => $case]);

        if($actionItemMember = $this->actionManager->actionItemMemberManager->getOne([
            'action' => $action,
            'item' => $item,
            'member' => $memberConnected,
        ])) {
            $this->actionManager->actionItemMemberManager->remove($actionItemMember);
            $data['action'] = 'un'.$case;
            $data['action_reverse'] = $case;
        } else {
            $actionItemMember = $this->actionManager->actionItemMemberManager->init();
            $actionItemMember->setAction($action);
            $actionItemMember->setItem($item);
            $actionItemMember->setMember($memberConnected);

            $this->actionManager->actionItemMemberManager->persist($actionItemMember);
            $data['action'] = $case;
            $data['action_reverse'] = 'un'.$case;
        }

        $data['entry'] = $item->toArray();
        $data['entry_entity'] = 'item';

        return new JsonResponse($data);
    }

    /**
     * Retrieve content with Readability.
     *
     * @ApiDoc(
     *     section="_ Item",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function readabilityAction(Request $request, ParameterBag $parameterBag, $id)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $parameterBag->set('action', $this->actionManager->getOne(['title' => 'readability']));
        $parameterBag->set('item', $this->itemManager->getOne(['id' => $id]));

        if($actionItem = $this->actionManager->actionItemManager->getOne([
            'action' => $parameterBag->get('action'),
            'item' => $parameterBag->get('item'),
        ])) {
        } else {
            $actionItem = $this->actionManager->actionItemManager->init();
            $actionItem->setAction($parameterBag->get('action'));
            $actionItem->setItem($parameterBag->get('item'));

            $this->actionManager->actionItemManager->persist($actionItem);
        }

        $this->itemManager->getContentFull($parameterBag->get('item'));

        return new JsonResponse($data);
    }

    /**
     * Send item by email.
     *
     * @ApiDoc(
     *     section="_ Item",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     *     parameters={
     *         {"name"="subject", "dataType"="string", "required"=true, "format"="default ""item title""", "description"=""},
     *         {"name"="recipient", "dataType"="string", "required"=true, "format"="email", "description"=""},
     *         {"name"="message", "dataType"="string", "required"=false, "format"="", "description"=""},
     *         {"name"="replyTo", "dataType"="string", "required"=false, "format"="email", "description"=""},
     *     },
     * )
     */
    public function emailAction(Request $request, ParameterBag $parameterBag, $id)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $parameterBag->set('action', $this->actionManager->getOne(['title' => 'email']));
        $parameterBag->set('item', $this->memberManager->getOne(['id' => $id]));

        if($actionItem = $this->actionManager->actionItemManager->getOne([
            'action' => $parameterBag->get('action'),
            'item' => $parameterBag->get('item'),
        ])) {
        } else {
            $actionItem = $this->actionManager->actionItemManager->init();
            $actionItem->setAction($parameterBag->get('action'));
            $actionItem->setItem($parameterBag->get('item'));

            $this->actionManager->actionItemManager->persist($actionItem);
        }

        return new JsonResponse($data);
    }
}
