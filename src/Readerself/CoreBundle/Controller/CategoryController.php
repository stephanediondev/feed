<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Readerself\CoreBundle\Controller\AbstractController;
use Readerself\CoreBundle\Manager\CategoryManager;
use Readerself\CoreBundle\Manager\ActionManager;

use Readerself\CoreBundle\Form\Type\CategoryType;

class CategoryController extends AbstractController
{
    protected $categoryManager;

    protected $actionManager;

    public function __construct(
        CategoryManager $categoryManager,
        ActionManager $actionManager
    ) {
        $this->categoryManager = $categoryManager;
        $this->actionManager = $actionManager;
    }

    /**
     * Retrieve all categories.
     *
     * @ApiDoc(
     *     section="_ Category",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     *     parameters={
     *         {"name"="sortDirection", "dataType"="string", "required"=false, "format"="""asc"" or ""desc"", default ""desc""", "description"=""},
     *         {"name"="page", "dataType"="integer", "required"=false, "format"="default ""1""", "description"="page number"},
     *         {"name"="perPage", "dataType"="integer", "required"=false, "format"="default ""100""", "description"="categories per page"},
     *         {"name"="excluded", "dataType"="integer", "required"=false, "format"="1 or 0", "description"="excluded categories"},
     *     },
     * )
     */
    public function indexAction(Request $request)
    {
        $data = [];
        $memberConnected = $this->validateToken($request);

        $parameters = [];

        if($request->query->get('excluded')) {
            if(!$memberConnected) {
                return new JsonResponse($data, 403);
            }
            $parameters['excluded'] = true;
            $parameters['member'] = $memberConnected;
        }

        if($request->query->get('feed')) {
            $parameters['feed'] = true;
        }

        $paginator= $this->get('knp_paginator');
        $paginator->setDefaultPaginatorOptions(['widgetParameterName' => 'page']);
        $pagination = $paginator->paginate(
            $this->categoryManager->getList($parameters),
            $page = $request->query->getInt('page', 1),
            $request->query->getInt('perPage', 100)
        );

        $data['entries'] = [];
        $index = 0;
        foreach($pagination as $result) {
            $category = $this->categoryManager->getOne(['id' => $result['id']]);
            $actions = $this->get('readerself_core_manager_action')->actionCategoryMemberManager->getList(['member' => $memberConnected, 'category' => $category]);

            $data['entries'][$index] = $category->toArray();
            foreach($actions as $action) {
                $data['entries'][$index][$action->getAction()->getTitle()] = true;
            }
            $index++;
        }
        $data['entries_entity'] = 'category';
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
     * Create a category.
     *
     * @ApiDoc(
     *     section="Category",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     *     parameters={
     *     },
     * )
     */
    public function createAction(Request $request)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $form = $this->createForm(CategoryType::class, $this->categoryManager->init());

        $form->submit($request->request->all(), false);

        if($form->isValid()) {
            $category_id = $this->categoryManager->persist($form->getData());

            $data['entry'] = $this->categoryManager->getOne(['id' => $category_id])->toArray();
            $data['entry_entity'] = 'category';

        } else {
            $errors = $form->getErrors(true);
            foreach($errors as $error) {
                $data['errors'][$error->getOrigin()->getName()] = $error->getMessage();
            }
        }

        return new JsonResponse($data);
    }

    /**
     * Retrieve a category.
     *
     * @ApiDoc(
     *     section="Category",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function readAction(Request $request, $id)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            //return new JsonResponse($data, 403);
        }

        $category = $this->categoryManager->getOne(['id' => $id]);

        if(!$category) {
            return new JsonResponse($data, 404);
        }

        $actions = $this->get('readerself_core_manager_action')->actionCategoryMemberManager->getList(['member' => $memberConnected, 'category' => $category]);

        $data['entry'] = $category->toArray();
        foreach($actions as $action) {
            $data['entry'][$action->getAction()->getTitle()] = true;
        }
        $data['entry_entity'] = 'category';

        return new JsonResponse($data);
    }

    /**
     * Update a category.
     *
     * @ApiDoc(
     *     section="Category",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function updateAction(Request $request, $id)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $category = $this->categoryManager->getOne(['id' => $id]);

        if(!$category) {
            return new JsonResponse($data, 404);
        }

        $data['entry'] = $category->toArray();
        $data['entry_entity'] = 'category';

        return new JsonResponse($data);
    }

    /**
     * Delete a category.
     *
     * @ApiDoc(
     *     section="Category",
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

        $category = $this->categoryManager->getOne(['id' => $id]);

        if(!$category) {
            return new JsonResponse($data, 404);
        }

        $data['entry'] = $category->toArray();
        $data['entry_entity'] = 'category';

        $this->categoryManager->remove($category);

        return new JsonResponse($data);
    }

    /**
     * Set "exclude" action / Remove "exclude" action.
     *
     * @ApiDoc(
     *     section="_ Category",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function excludeAction(Request $request, $id)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $category = $this->categoryManager->getOne(['id' => $id]);

        if(!$category) {
            return new JsonResponse($data, 404);
        }

        $action = $this->actionManager->getOne(['title' => 'exclude']);

        if($actionCategoryMember = $this->actionManager->actionCategoryMemberManager->getOne([
            'action' => $action,
            'category' => $category,
            'member' => $memberConnected,
        ])) {
            $this->actionManager->actionCategoryMemberManager->remove($actionCategoryMember);
            $data['action'] = 'include';
            $data['action_reverse'] = 'exclude';
        } else {
            $actionCategoryMember = $this->actionManager->actionCategoryMemberManager->init();
            $actionCategoryMember->setAction($action);
            $actionCategoryMember->setCategory($category);
            $actionCategoryMember->setMember($memberConnected);

            $this->actionManager->actionCategoryMemberManager->persist($actionCategoryMember);
            $data['action'] = 'exclude';
            $data['action_reverse'] = 'include';
        }

        $data['entry'] = $category->toArray();
        $data['entry_entity'] = 'category';

        return new JsonResponse($data);
    }
}
