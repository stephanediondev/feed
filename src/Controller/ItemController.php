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
use App\Model\QueryParameterFilterModel;
use App\Model\QueryParameterPageModel;
use App\Model\QueryParameterSortModel;
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

        $filters = new QueryParameterFilterModel($request->query->all('filter'));

        $parameters = [];
        $parameters['member'] = $this->getUser();

        if ($filters->getBool('starred')) {
            $parameters['starred'] = true;
        }

        if ($filters->getBool('unread')) {
            $parameters['unread'] = true;
        }

        if ($filters->getBool('geolocation')) {
            $parameters['geolocation'] = true;
        }

        if ($filters->getInt('feed')) {
            if ($feed = $this->feedManager->getOne(['id' => $filters->getInt('feed')])) {
                $parameters['feed'] = $filters->getInt('feed');
                $data['entry'] = $feed->toArray();
                $data['entry_entity'] = 'feed';
            }
        }

        if ($filters->getInt('author')) {
            if ($author = $this->authorManager->getOne(['id' => $filters->getInt('author')])) {
                $parameters['author'] = $filters->getInt('author');
                $data['entry'] = $author->toArray();
                $data['entry_entity'] = 'author';
            }
        }

        if ($filters->getInt('category')) {
            if ($category = $this->categoryManager->getOne(['id' => $filters->getInt('category')])) {
                $parameters['category'] = $filters->getInt('category');
                $data['entry'] = $category->toArray();
                $data['entry_entity'] = 'category';
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
            $parameters['sortDirection'] = 'DESC';
            $parameters['sortField'] = 'itm.date';
        }

        $parameters['returnQueryBuilder'] = true;

        $pagination = $this->paginateAbstract($this->itemManager->getList($parameters));

        $data['entries_entity'] = 'item';
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

        if ($this->getUser()->getId()) {
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

        $filters = new QueryParameterFilterModel($request->query->all('filter'));

        $parameters = [];

        $parameters['member'] = $this->getUser();

        $parameters['unread'] = true;

        if ($filters->getBool('starred')) {
            $parameters['starred'] = true;
        }

        if ($filters->getInt('feed')) {
            $parameters['feed'] = $filters->getInt('feed');
        }

        if ($filters->getInt('author')) {
            $parameters['author'] = $filters->getInt('author');
        }

        if ($filters->getInt('category')) {
            $parameters['category'] = $filters->getInt('category');
        }

        if ($filters->getInt('age')) {
            $parameters['age'] = $filters->getInt('age');
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
