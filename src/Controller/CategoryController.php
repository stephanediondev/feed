<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Entity\ActionCategory;
use App\Entity\Category;
use App\Entity\Member;
use App\Form\Type\CategoryType;
use App\Manager\ActionCategoryManager;
use App\Manager\ActionManager;
use App\Manager\CategoryManager;
use App\Model\QueryParameterFilterModel;
use App\Model\QueryParameterSortModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api', name: 'api_categories_', priority: 15)]
class CategoryController extends AbstractAppController
{
    private ActionManager $actionManager;
    private ActionCategoryManager $actionCategoryManager;
    private CategoryManager $categoryManager;

    public function __construct(ActionManager $actionManager, ActionCategoryManager $actionCategoryManager, CategoryManager $categoryManager)
    {
        $this->actionManager = $actionManager;
        $this->actionCategoryManager = $actionCategoryManager;
        $this->categoryManager = $categoryManager;
    }

    #[Route(path: '/categories', name: 'index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $data = [];

        $this->denyAccessUnlessGranted('LIST', 'category');

        $filters = new QueryParameterFilterModel($request->query->all('filter'));

        $parameters = [];

        if ($filters->getBool('trendy')) {
            $parameters['trendy'] = true;

            if ($this->getMember()) {
                $parameters['member'] = $this->getMember();
            }

            $results = $this->categoryManager->getList($parameters);

            $data['entries'] = [];

            $max = false;
            foreach ($results as $row) {
                if (!$max) {
                    $max = $row['count'];
                }
                $data['entries'][$row['ref']] = ['count' => $row['count'], 'id' => $row['id']];
            }
            //ksort($data['entries']);

            foreach ($data['entries'] as $k => $v) {
                $percent = ($v['count'] * 100) / $max;
                $percent = $percent - ($percent % 10);
                $percent = intval($percent) + 100;
                $data['entries'][$k]['percent'] = $percent;
            }
        } else {
            if ($filters->getBool('excluded')) {
                $parameters['excluded'] = true;
                $parameters['member'] = $this->getMember();
            }

            if ($filters->getBool('usedbyfeeds')) {
                $parameters['usedbyfeeds'] = true;
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
                $parameters['sortField'] = 'cat.title';
            }

            $parameters['returnQueryBuilder'] = true;

            $pagination = $this->paginateAbstract($this->categoryManager->getList($parameters));

            $data['entries_entity'] = 'category';
            $data = array_merge($data, $this->getEntriesInfo($pagination));

            $data['entries'] = [];

            $ids = [];
            foreach ($pagination as $result) {
                $ids[] = $result['id'];
            }

            $results = $this->actionCategoryManager->getList(['member' => $this->getMember(), 'categories' => $ids])->getResult();
            $actions = [];
            foreach ($results as $actionCategory) {
                $actions[$actionCategory->getCategory()->getId()][] = $actionCategory;
            }

            foreach ($pagination as $result) {
                $category = $this->categoryManager->getOne(['id' => $result['id']]);
                if ($category) {
                    $entry = $category->toArray();

                    if (true === isset($actions[$result['id']])) {
                        foreach ($actions[$result['id']] as $action) {
                            $entry[$action->getAction()->getTitle()] = true;
                        }
                    }

                    $data['entries'][] = $entry;
                }
            }
        }

        return $this->jsonResponse($data);
    }

    #[Route(path: '/categories', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = [];

        $this->denyAccessUnlessGranted('CREATE', 'category');

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $content = $this->getContent($request);
        $form->submit($content);

        if ($form->isValid()) {
            $this->categoryManager->persist($form->getData());

            $data['entry'] = $category->toArray();
            $data['entry_entity'] = 'category';
        } else {
            $data = $this->getFormErrors($form);
            return $this->jsonResponse($data, JsonResponse::HTTP_BAD_REQUEST);
        }

        return $this->jsonResponse($data, JsonResponse::HTTP_CREATED);
    }

    #[Route('/category/{id}', name: 'read', methods: ['GET'])]
    public function read(Request $request, int $id): JsonResponse
    {
        $data = [];

        $category = $this->categoryManager->getOne(['id' => $id]);

        if (!$category) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('READ', $category);

        $actions = $this->actionCategoryManager->getList(['member' => $this->getMember(), 'category' => $category])->getResult();

        $data['entry'] = $category->toArray();
        foreach ($actions as $action) {
            $data['entry'][$action->getAction()->getTitle()] = true;
        }
        $data['entry_entity'] = 'category';

        return $this->jsonResponse($data);
    }

    #[Route('/category/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $data = [];

        $category = $this->categoryManager->getOne(['id' => $id]);

        if (!$category) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('UPDATE', $category);

        $form = $this->createForm(CategoryType::class, $category);

        $content = $this->getContent($request);
        $form->submit($content);

        if ($form->isValid()) {
            $this->categoryManager->persist($form->getData());

            $data['entry'] = $category->toArray();
            $data['entry_entity'] = 'category';
        } else {
            $data = $this->getFormErrors($form);
            return $this->jsonResponse($data, JsonResponse::HTTP_BAD_REQUEST);
        }

        return $this->jsonResponse($data);
    }

    #[Route('/category/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, int $id): JsonResponse
    {
        $data = [];

        $category = $this->categoryManager->getOne(['id' => $id]);

        if (!$category) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('DELETE', $category);

        $data['entry'] = $category->toArray();
        $data['entry_entity'] = 'category';

        $this->categoryManager->remove($category);

        return $this->jsonResponse($data);
    }

    #[Route('/category/action/exclude/{id}', name: 'action_exclude', methods: ['GET'])]
    public function actionExclude(Request $request, int $id): JsonResponse
    {
        return $this->setAction('exclude', $request, $id);
    }

    private function setAction(string $case, Request $request, int $id): JsonResponse
    {
        $data = [];

        $category = $this->categoryManager->getOne(['id' => $id]);

        if (!$category) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $action = $this->actionManager->getOne(['title' => $case]);

        if (!$action) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('ACTION_'.strtoupper($case), $category);

        if ($actionCategory = $this->actionCategoryManager->getOne([
            'action' => $action,
            'category' => $category,
            'member' => $this->getMember(),
        ])) {
            $this->actionCategoryManager->remove($actionCategory);

            $data['action'] = $action->getReverse() ? $action->getReverse()->getTitle() : null;
            $data['action_reverse'] = $action->getTitle();

            if ($action->getReverse()) {
                if ($actionCategoryReverse = $this->actionCategoryManager->getOne([
                    'action' => $action->getReverse(),
                    'category' => $category,
                    'member' => $this->getMember(),
                ])) {
                } else {
                    $actionCategoryReverse = new ActionCategory();
                    $actionCategoryReverse->setAction($action->getReverse());
                    $actionCategoryReverse->setCategory($category);
                    $actionCategoryReverse->setMember($this->getMember());
                    $this->actionCategoryManager->persist($actionCategoryReverse);
                }
            }
        } else {
            $actionCategory = new ActionCategory();
            $actionCategory->setAction($action);
            $actionCategory->setCategory($category);
            $actionCategory->setMember($this->getMember());
            $this->actionCategoryManager->persist($actionCategory);

            $data['action'] = $action->getTitle();
            $data['action_reverse'] = $action->getReverse() ? $action->getReverse()->getTitle() : null;

            if ($action->getReverse()) {
                if ($actionCategoryReverse = $this->actionCategoryManager->getOne([
                    'action' => $action->getReverse(),
                    'category' => $category,
                    'member' => $this->getMember(),
                ])) {
                    $this->actionCategoryManager->remove($actionCategoryReverse);
                }
            }
        }

        $data['entry'] = $category->toArray();
        $data['entry_entity'] = 'category';

        return $this->jsonResponse($data);
    }
}
