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

        $filtersModel = new QueryParameterFilterModel($request->query->all('filter'));

        $parameters = [];
        $parameters['member'] = $this->getMember();

        if ($filtersModel->getBool('starred')) {
            $parameters['starred'] = true;
        }

        if ($filtersModel->getBool('unread')) {
            $parameters['unread'] = true;
        }

        if ($filtersModel->getBool('geolocation')) {
            $parameters['geolocation'] = true;
        }

        if ($filtersModel->getInt('feed')) {
            if ($feed = $this->feedManager->getOne(['id' => $filtersModel->getInt('feed')])) {
                $parameters['feed'] = $filtersModel->getInt('feed');
                $data['entry'] = $feed->toArray();
                $data['entry_entity'] = 'feed';
            }
        }

        if ($filtersModel->getInt('author')) {
            if ($author = $this->authorManager->getOne(['id' => $filtersModel->getInt('author')])) {
                $parameters['author'] = $filtersModel->getInt('author');
                $data['entry'] = $author->toArray();
                $data['entry_entity'] = 'author';
            }
        }

        if ($filtersModel->getInt('category')) {
            if ($category = $this->categoryManager->getOne(['id' => $filtersModel->getInt('category')])) {
                $parameters['category'] = $filtersModel->getInt('category');
                $data['entry'] = $category->toArray();
                $data['entry_entity'] = 'category';
            }
        }

        if ($filtersModel->getInt('days')) {
            $parameters['days'] = $filtersModel->getInt('days');
        }

        $sortModel = new QueryParameterSortModel($request->query->get('sort'));

        if ($sortGet = $sortModel->get()) {
            $parameters['sortDirection'] = $sortGet['direction'];
            $parameters['sortField'] = $sortGet['field'];
        } else {
            $parameters['sortDirection'] = 'DESC';
            $parameters['sortField'] = 'itm.date';
        }

        $parameters['returnQueryBuilder'] = true;

        $pagination = $this->paginateAbstract($request, $this->itemManager->getList($parameters));

        $data['entries_entity'] = 'item';
        $data = array_merge($data, $this->jsonApi($request, $pagination, $sortModel, $filtersModel));

        if ($this->getMember() && $this->getMember()->getId()) {
            $data['unread'] = $this->memberManager->countUnread($this->getMember()->getId());
        }

        $data['entries'] = [];

        $ids = [];
        foreach ($pagination as $result) {
            $ids[] = $result['id'];
        }

        $results = $this->actionItemManager->getList(['member' => $this->getMember(), 'items' => $ids])->getResult();
        $actions = [];
        foreach ($results as $actionItem) {
            $actions[$actionItem->getItem()->getId()][] = $actionItem;
        }

        $results = $this->categoryManager->itemCategoryManager->getList(['member' => $this->getMember(), 'items' => $ids])->getResult();
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

        return $this->jsonResponse($data);
    }

    #[Route('/item/{id}', name: 'read', methods: ['GET'])]
    public function read(Request $request, int $id): JsonResponse
    {
        $data = [];

        $item = $this->itemManager->getOne(['id' => $id]);

        if (!$item) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('READ', $item);

        $actions = $this->actionItemManager->getList(['member' => $this->getMember(), 'item' => $item])->getResult();

        $categories = [];
        foreach ($this->categoryManager->itemCategoryManager->getList(['member' => $this->getMember(), 'item' => $item])->getResult() as $itemCategory) {
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

        return $this->jsonResponse($data);
    }

    #[Route('/item/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, int $id): JsonResponse
    {
        $data = [];

        $item = $this->itemManager->getOne(['id' => $id]);

        if (!$item) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('DELETE', $item);

        $data['entry'] = $item->toArray();
        $data['entry_entity'] = 'item';

        $this->itemManager->remove($item);

        return $this->jsonResponse($data);
    }

    #[Route('/items/markallasread', name: 'markallasread', methods: ['GET'])]
    public function markallasread(Request $request): JsonResponse
    {
        $data = [];

        $filtersModel = new QueryParameterFilterModel($request->query->all('filter'));

        $parameters = [];

        $parameters['member'] = $this->getMember();

        $parameters['unread'] = true;

        if ($filtersModel->getBool('starred')) {
            $parameters['starred'] = true;
        }

        if ($filtersModel->getInt('feed')) {
            $parameters['feed'] = $filtersModel->getInt('feed');
        }

        if ($filtersModel->getInt('author')) {
            $parameters['author'] = $filtersModel->getInt('author');
        }

        if ($filtersModel->getInt('category')) {
            $parameters['category'] = $filtersModel->getInt('category');
        }

        if ($filtersModel->getInt('age')) {
            $parameters['age'] = $filtersModel->getInt('age');
        }

        $parameters['sortField'] = 'itm.id';
        $parameters['sortDirection'] = 'DESC';

        $this->itemManager->readAll($parameters);

        if ($this->getMember() && $this->getMember()->getId()) {
            $data['unread'] = $this->memberManager->countUnread($this->getMember()->getId());
        }

        return $this->jsonResponse($data);
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

        $item = $this->itemManager->getOne(['id' => $id]);

        if (!$item) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $action = $this->actionManager->getOne(['title' => $case]);

        if (!$action) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('ACTION_'.strtoupper($case), $item);

        if ($actionItem = $this->actionItemManager->getOne([
            'action' => $action,
            'item' => $item,
            'member' => $this->getMember(),
        ])) {
            $this->actionItemManager->remove($actionItem);

            $data['action'] = $action->getReverse() ? $action->getReverse()->getTitle() : null;
            $data['action_reverse'] = $action->getTitle();

            if ($action->getReverse()) {
                if ($actionItemReverse = $this->actionItemManager->getOne([
                    'action' => $action->getReverse(),
                    'item' => $item,
                    'member' => $this->getMember(),
                ])) {
                } else {
                    $actionItemReverse = new ActionItem();
                    $actionItemReverse->setAction($action->getReverse());
                    $actionItemReverse->setItem($item);
                    $actionItemReverse->setMember($this->getMember());
                    $this->actionItemManager->persist($actionItemReverse);
                }
            }
        } else {
            $actionItem = new ActionItem();
            $actionItem->setAction($action);
            $actionItem->setItem($item);
            $actionItem->setMember($this->getMember());
            $this->actionItemManager->persist($actionItem);

            $data['action'] = $action->getTitle();
            $data['action_reverse'] = $action->getReverse() ? $action->getReverse()->getTitle() : null;

            if ($action->getReverse()) {
                if ($actionItemReverse = $this->actionItemManager->getOne([
                    'action' => $action->getReverse(),
                    'item' => $item,
                    'member' => $this->getMember(),
                ])) {
                    $this->actionItemManager->remove($actionItemReverse);
                }
            }
        }

        $data['entry'] = $item->toArray();
        $data['entry_entity'] = 'item';

        if ($case == 'read' && $this->getMember() && $this->getMember()->getId()) {
            $data['unread'] = $this->memberManager->countUnread($this->getMember()->getId());
        }

        return $this->jsonResponse($data);
    }
}
