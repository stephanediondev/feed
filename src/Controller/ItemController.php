<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Entity\ActionItem;
use App\Entity\Member;
use App\Helper\CleanHelper;
use App\Manager\ActionItemManager;
use App\Manager\ActionManager;
use App\Manager\AuthorManager;
use App\Manager\CategoryManager;
use App\Manager\FeedManager;
use App\Manager\ItemManager;
use App\Manager\MemberManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api', name: 'api_items_', priority: 15)]
class ItemController extends AbstractAppController
{
    private ActionManager $actionManager;
    private ActionItemManager $actionItemManager;
    private FeedManager $feedManager;
    private AuthorManager $authorManager;
    private CategoryManager $categoryManager;
    private ItemManager $itemManager;
    private MemberManager $memberManager;

    public function __construct(ActionManager $actionManager, ActionItemManager $actionItemManager, FeedManager $feedManager, AuthorManager $authorManager, CategoryManager $categoryManager, ItemManager $itemManager, MemberManager $memberManager)
    {
        $this->actionManager = $actionManager;
        $this->actionItemManager = $actionItemManager;
        $this->feedManager = $feedManager;
        $this->authorManager = $authorManager;
        $this->categoryManager = $categoryManager;
        $this->itemManager = $itemManager;
        $this->memberManager = $memberManager;
    }

    #[Route(path: '/items', name: 'index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $data = [];

        $this->denyAccessUnlessGranted('LIST', 'item');

        if (false === $this->getUser() instanceof Member) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $parameters = [];
        $parameters['member'] = $this->getUser();

        if ($request->query->get('starred')) {
            $parameters['starred'] = $request->query->get('starred');
        }

        if ($request->query->get('unread')) {
            $parameters['unread'] = (bool) $request->query->get('unread');
        }

        if ($request->query->get('geolocation')) {
            $parameters['geolocation'] = (bool) $request->query->get('geolocation');
        }

        if ($request->query->get('feed')) {
            if ($feed = $this->feedManager->getOne(['id' => (int) $request->query->get('feed')])) {
                $parameters['feed'] = (int) $request->query->get('feed');
                $data['entry'] = $feed->toArray();
                $data['entry_entity'] = 'feed';
            }
        }

        if ($request->query->get('author')) {
            if ($author = $this->authorManager->getOne(['id' => (int) $request->query->get('author')])) {
                $parameters['author'] = (int) $request->query->get('author');
                $data['entry'] = $author->toArray();
                $data['entry_entity'] = 'author';
            }
        }

        if ($request->query->get('category')) {
            if ($category = $this->categoryManager->getOne(['id' => (int) $request->query->get('category')])) {
                $parameters['category'] = (int) $request->query->get('category');
                $data['entry'] = $category->toArray();
                $data['entry_entity'] = 'category';
            }
        }

        if ($request->query->get('unread')) {
            $page = 1;
        } else {
            $page = $request->query->getInt('page', 1);
        }

        if ($request->query->get('days')) {
            $parameters['days'] = (int) $request->query->get('days');
        }

        $fields = ['title' => 'itm.title', 'date' => 'itm.date'];
        if ($request->query->get('sortField') && array_key_exists(strval($request->query->get('sortField')), $fields)) {
            $parameters['sortField'] = $fields[$request->query->get('sortField')];
        } else {
            $parameters['sortField'] = 'itm.date';
        }

        $directions = ['ASC', 'DESC'];
        if ($request->query->get('sortDirection') && in_array($request->query->get('sortDirection'), $directions)) {
            $parameters['sortDirection'] = $request->query->get('sortDirection');
        } else {
            $parameters['sortDirection'] = 'DESC';
        }

        $parameters['returnQueryBuilder'] = true;

        $pagination = $this->paginateAbstract($this->itemManager->getList($parameters), $page = intval($request->query->getInt('page', 1)), intval($request->query->getInt('perPage', 20)));

        $data['entries_entity'] = 'item';
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

        if ($request->query->get('unread')) {
            $data['unread'] = $pagination->getTotalItemCount();
        } elseif ($this->getUser()->getId()) {
            $data['unread'] = $this->memberManager->countUnread($this->getUser()->getId());
        }

        $data['entries'] = [];

        $ids = [];
        foreach ($pagination as $result) {
            $ids[] = $result['id'];
        }

        $results = $this->actionItemManager->getList(['member' => $this->getUser(), 'items' => $ids])->getResult();
        $actions = [];
        foreach ($results as $actionItem) {
            $actions[$actionItem->getItem()->getId()][] = $actionItem;
        }

        $results = $this->categoryManager->itemCategoryManager->getList(['member' => $this->getUser(), 'items' => $ids])->getResult();
        $categories = [];
        foreach ($results as $itemCategory) {
            $categories[$itemCategory->getItem()->getId()][] = $itemCategory->toArray();
        }

        foreach ($pagination as $result) {
            $item = $this->itemManager->getOne(['id' => $result['id']]);
            if ($item) {
                $entry = $item->toArray();

                if (true === isset($actions[$result['id']])) {
                    foreach ($actions[$result['id']] as $action) {
                        $entry[$action->getAction()->getTitle()] = true;
                    }
                }

                if (true === isset($categories[$result['id']])) {
                    $entry['categories'] = $categories[$result['id']];
                }

                $entry['enclosures'] = $this->itemManager->prepareEnclosures($item, $request);

                $entry['content'] = CleanHelper::cleanContent($item->getContent(), 'display');

                $data['entries'][] = $entry;
            }
        }

        return new JsonResponse($data);
    }

    #[Route('/item/{id}', name: 'read', methods: ['GET'])]
    public function read(Request $request, int $id): JsonResponse
    {
        $data = [];

        $item = $this->itemManager->getOne(['id' => $id]);

        if (!$item) {
            return new JsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('READ', $item);

        $actions = $this->actionItemManager->getList(['member' => $this->getUser(), 'item' => $item])->getResult();

        $categories = [];
        foreach ($this->categoryManager->itemCategoryManager->getList(['member' => $this->getUser(), 'item' => $item])->getResult() as $itemCategory) {
            $categories[] = $itemCategory->toArray();
        }

        $data['entry'] = $item->toArray();
        foreach ($actions as $action) {
            $data['entry'][$action->getAction()->getTitle()] = true;
        }
        $data['entry']['categories'] = $categories;
        $data['entry']['enclosures'] = $this->itemManager->prepareEnclosures($item, $request);

        $data['entry']['content'] = CleanHelper::cleanContent($item->getContent(), 'display');

        $data['entry_entity'] = 'item';

        return new JsonResponse($data);
    }

    #[Route('/item/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, int $id): JsonResponse
    {
        $data = [];

        $item = $this->itemManager->getOne(['id' => $id]);

        if (!$item) {
            return new JsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('DELETE', $item);

        $data['entry'] = $item->toArray();
        $data['entry_entity'] = 'item';

        $this->itemManager->remove($item);

        return new JsonResponse($data);
    }

    #[Route('/items/markallasread', name: 'markallasread', methods: ['GET'])]
    public function markallasread(Request $request): JsonResponse
    {
        $data = [];

        if (false === $this->getUser() instanceof Member) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $parameters = [];
        $parameters['member'] = $this->getUser();

        $parameters['unread'] = (bool) $request->query->get('unread');

        if ($request->query->get('starred')) {
            $parameters['starred'] = $request->query->get('starred');
        }

        if ($request->query->get('feed')) {
            $parameters['feed'] = (int) $request->query->get('feed');
        }

        if ($request->query->get('author')) {
            $parameters['author'] = (int) $request->query->get('author');
        }

        if ($request->query->get('category')) {
            $parameters['category'] = (int) $request->query->get('category');
        }

        if ($request->query->get('age')) {
            $parameters['age'] = (int) $request->query->get('age');
        }

        $parameters['sortField'] = 'itm.id';

        $parameters['sortDirection'] = 'DESC';

        $this->itemManager->readAll($parameters);

        if ($this->getUser()->getId()) {
            $data['unread'] = $this->memberManager->countUnread($this->getUser()->getId());
        }

        return new JsonResponse($data);
    }

    #[Route('/item/action/read/{id}', name: 'action_read', methods: ['GET'])]
    public function actionRead(Request $request, int $id): JsonResponse
    {
        return $this->setAction('read', $request, $id);
    }

    #[Route('/item/action/star/{id}', name: 'action_star', methods: ['GET'])]
    public function actionStar(Request $request, int $id): JsonResponse
    {
        return $this->setAction('star', $request, $id);
    }

    private function setAction(string $case, Request $request, int $id): JsonResponse
    {
        $data = [];

        if (false === $this->getUser() instanceof Member) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $item = $this->itemManager->getOne(['id' => $id]);

        if (!$item) {
            return new JsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $action = $this->actionManager->getOne(['title' => $case]);

        if (!$action) {
            return new JsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        if ($actionItem = $this->actionItemManager->getOne([
            'action' => $action,
            'item' => $item,
            'member' => $this->getUser(),
        ])) {
            $this->actionItemManager->remove($actionItem);

            $data['action'] = $action->getReverse() ? $action->getReverse()->getTitle() : null;
            $data['action_reverse'] = $action->getTitle();

            if ($action->getReverse()) {
                if ($actionItemReverse = $this->actionItemManager->getOne([
                    'action' => $action->getReverse(),
                    'item' => $item,
                    'member' => $this->getUser(),
                ])) {
                } else {
                    $actionItemReverse = new ActionItem();
                    $actionItemReverse->setAction($action->getReverse());
                    $actionItemReverse->setItem($item);
                    $actionItemReverse->setMember($this->getUser());
                    $this->actionItemManager->persist($actionItemReverse);
                }
            }
        } else {
            $actionItem = new ActionItem();
            $actionItem->setAction($action);
            $actionItem->setItem($item);
            $actionItem->setMember($this->getUser());
            $this->actionItemManager->persist($actionItem);

            $data['action'] = $action->getTitle();
            $data['action_reverse'] = $action->getReverse() ? $action->getReverse()->getTitle() : null;

            if ($action->getReverse()) {
                if ($actionItemReverse = $this->actionItemManager->getOne([
                    'action' => $action->getReverse(),
                    'item' => $item,
                    'member' => $this->getUser(),
                ])) {
                    $this->actionItemManager->remove($actionItemReverse);
                }
            }
        }

        $data['entry'] = $item->toArray();
        $data['entry_entity'] = 'item';

        if ($case == 'read' && $this->getUser()->getId()) {
            $data['unread'] = $this->memberManager->countUnread($this->getUser()->getId());
        }

        return new JsonResponse($data);
    }
}
