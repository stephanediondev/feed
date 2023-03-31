<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Entity\ActionCategory;
use App\Entity\Category;
use App\Form\Type\CategoryType;
use App\Manager\ActionCategoryManager;
use App\Manager\ActionManager;
use App\Manager\CategoryManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api', name: 'api_categories_')]
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
        $memberConnected = $this->validateToken($request);

        $parameters = [];

        if ($request->query->get('trendy')) {
            $parameters['trendy'] = true;

            if ($memberConnected) {
                $parameters['member'] = $memberConnected;
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
            if ($request->query->get('excluded')) {
                if (!$memberConnected) {
                    return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
                }
                $parameters['excluded'] = true;
                $parameters['member'] = $memberConnected;
            }

            if ($request->query->get('usedbyfeeds')) {
                $parameters['usedbyfeeds'] = true;
            }

            if ($request->query->get('days')) {
                $parameters['days'] = (int) $request->query->get('days');
            }

            $fields = ['title' => 'cat.title', 'date_created' => 'cat.dateCreated'];
            if ($request->query->get('sortField') && array_key_exists(strval($request->query->get('sortField')), $fields)) {
                $parameters['sortField'] = $fields[$request->query->get('sortField')];
            } else {
                $parameters['sortField'] = 'cat.title';
            }

            $directions = ['ASC', 'DESC'];
            if ($request->query->get('sortDirection') && in_array($request->query->get('sortDirection'), $directions)) {
                $parameters['sortDirection'] = $request->query->get('sortDirection');
            } else {
                $parameters['sortDirection'] = 'ASC';
            }

            $parameters['returnQueryBuilder'] = true;

            $pagination = $this->paginateAbstract($this->categoryManager->getList($parameters), $page = intval($request->query->getInt('page', 1)), intval($request->query->getInt('perPage', 20)));

            $data['entries_entity'] = 'category';
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

            $ids = [];
            foreach ($pagination as $result) {
                $ids[] = $result['id'];
            }

            $results = $this->actionCategoryManager->getList(['member' => $memberConnected, 'categories' => $ids])->getResult();
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

        return new JsonResponse($data);
    }

    public function create(Request $request): JsonResponse
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $content = $this->getContent($request);
        $form->submit($content);

        if ($form->isValid()) {
            $this->categoryManager->persist($form->getData());

            $data['entry'] = $category->toArray();
            $data['entry_entity'] = 'category';
        } else {
            $errors = $form->getErrors(true);
            foreach ($errors as $error) {
                if (method_exists($error, 'getOrigin') && method_exists($error, 'getMessage')) {
                    $data['errors'][$error->getOrigin()->getName()] = $error->getMessage();
                }
            }
            return new JsonResponse($data, JsonResponse::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($data, JsonResponse::HTTP_CREATED);
    }

    #[Route('/category/{id}', name: 'read', methods: ['GET'])]
    public function read(Request $request, int $id): JsonResponse
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            //return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $category = $this->categoryManager->getOne(['id' => $id]);

        if (!$category) {
            return new JsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $actions = $this->actionCategoryManager->getList(['member' => $memberConnected, 'category' => $category])->getResult();

        $data['entry'] = $category->toArray();
        foreach ($actions as $action) {
            $data['entry'][$action->getAction()->getTitle()] = true;
        }
        $data['entry_entity'] = 'category';

        return new JsonResponse($data);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $category = $this->categoryManager->getOne(['id' => $id]);

        if (!$category) {
            return new JsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $data['entry'] = $category->toArray();
        $data['entry_entity'] = 'category';

        return new JsonResponse($data);
    }

    public function delete(Request $request, int $id): JsonResponse
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        if (!$memberConnected->getAdministrator()) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $category = $this->categoryManager->getOne(['id' => $id]);

        if (!$category) {
            return new JsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $data['entry'] = $category->toArray();
        $data['entry_entity'] = 'category';

        $this->categoryManager->remove($category);

        return new JsonResponse($data);
    }

    #[Route('/category/action/exclude/{id}', name: 'action_exclude', methods: ['GET'])]
    public function actionExclude(Request $request, int $id): JsonResponse
    {
        return $this->setAction('exclude', $request, $id);
    }

    private function setAction(string $case, Request $request, int $id): JsonResponse
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $category = $this->categoryManager->getOne(['id' => $id]);

        if (!$category) {
            return new JsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $action = $this->actionManager->getOne(['title' => $case]);

        if (!$action) {
            return new JsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        if ($actionCategory = $this->actionCategoryManager->getOne([
            'action' => $action,
            'category' => $category,
            'member' => $memberConnected,
        ])) {
            $this->actionCategoryManager->remove($actionCategory);

            $data['action'] = $action->getReverse() ? $action->getReverse()->getTitle() : null;
            $data['action_reverse'] = $action->getTitle();

            if ($action->getReverse()) {
                if ($actionCategoryReverse = $this->actionCategoryManager->getOne([
                    'action' => $action->getReverse(),
                    'category' => $category,
                    'member' => $memberConnected,
                ])) {
                } else {
                    $actionCategoryReverse = new ActionCategory();
                    $actionCategoryReverse->setAction($action->getReverse());
                    $actionCategoryReverse->setCategory($category);
                    $actionCategoryReverse->setMember($memberConnected);
                    $this->actionCategoryManager->persist($actionCategoryReverse);
                }
            }
        } else {
            $actionCategory = new ActionCategory();
            $actionCategory->setAction($action);
            $actionCategory->setCategory($category);
            $actionCategory->setMember($memberConnected);
            $this->actionCategoryManager->persist($actionCategory);

            $data['action'] = $action->getTitle();
            $data['action_reverse'] = $action->getReverse() ? $action->getReverse()->getTitle() : null;

            if ($action->getReverse()) {
                if ($actionCategoryReverse = $this->actionCategoryManager->getOne([
                    'action' => $action->getReverse(),
                    'category' => $category,
                    'member' => $memberConnected,
                ])) {
                    $this->actionCategoryManager->remove($actionCategoryReverse);
                }
            }
        }

        $data['entry'] = $category->toArray();
        $data['entry_entity'] = 'category';

        return new JsonResponse($data);
    }
}
