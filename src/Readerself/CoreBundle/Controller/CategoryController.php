<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Readerself\CoreBundle\Controller\AbstractController;

use Readerself\CoreBundle\Form\Type\CategoryType;

class CategoryController extends AbstractController
{
    /**
     * Retrieve all categories.
     *
     * @ApiDoc(
     *     section="Category",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     *     parameters={
     *         {"name"="sortField", "dataType"="string", "required"=false, "format"="""title"" or ""date_created"", default ""title""", "description"=""},
     *         {"name"="sortDirection", "dataType"="string", "required"=false, "format"="""ASC"" or ""DESC"", default ""DESC""", "description"=""},
     *         {"name"="page", "dataType"="integer", "required"=false, "format"="default ""1""", "description"="page number"},
     *         {"name"="perPage", "dataType"="integer", "required"=false, "format"="default ""100""", "description"="categories per page"},
     *         {"name"="recent", "dataType"="integer", "required"=false, "format"="1 or 0", "description"="recent categories"},
     *         {"name"="excluded", "dataType"="integer", "required"=false, "format"="1 or 0", "description"="excluded categories"},
     *         {"name"="usedbyfeeds", "dataType"="integer", "required"=false, "format"="1 or 0", "description"="categories used by feeds"},
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

        if($request->query->get('usedbyfeeds')) {
            $parameters['usedbyfeeds'] = true;
        }

        if($request->query->get('days')) {
            $parameters['days'] = (int) $request->query->get('days');
        }

        $fields = ['title' => 'cat.title', 'date_created' => 'cat.dateCreated'];
        if($request->query->get('sortField') && array_key_exists($request->query->get('sortField'), $fields)) {
            $parameters['sortField'] = $fields[$request->query->get('sortField')];
        } else {
            $parameters['sortField'] = 'cat.title';
        }

        $directions = ['ASC', 'DESC'];
        if($request->query->get('sortDirection') && in_array($request->query->get('sortDirection'), $directions)) {
            $parameters['sortDirection'] = $request->query->get('sortDirection');
        } else {
            $parameters['sortDirection'] = 'ASC';
        }

        $paginator= $this->get('knp_paginator');
        $paginator->setDefaultPaginatorOptions(['widgetParameterName' => 'page']);
        $pagination = $paginator->paginate(
            $this->categoryManager->getList($parameters),
            $page = $request->query->getInt('page', 1),
            $request->query->getInt('perPage', 100)
        );

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

        $data['entries'] = [];

        $index = 0;
        foreach($pagination as $result) {
            $category = $this->categoryManager->getOne(['id' => $result['id']]);
            $actions = $this->get('readerself_core_manager_action')->actionCategoryMemberManager->getList(['member' => $memberConnected, 'category' => $category])->getResult();

            $data['entries'][$index] = $category->toArray();
            foreach($actions as $action) {
                $data['entries'][$index][$action->getAction()->getTitle()] = true;
            }
            $index++;
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

        $actions = $this->actionManager->actionCategoryMemberManager->getList(['member' => $memberConnected, 'category' => $category])->getResult();

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

        if(!$memberConnected->getAdministrator()) {
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
     *     section="Category",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function actionExcludeAction(Request $request, $id)
    {
        return $this->setAction('exclude', $request, $id);
    }

    private function setAction($case, Request $request, $id)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $category = $this->categoryManager->getOne(['id' => $id]);

        if(!$category) {
            return new JsonResponse($data, 404);
        }

        $action = $this->actionManager->getOne(['title' => $case]);

        if($actionCategoryMember = $this->actionManager->actionCategoryMemberManager->getOne([
            'action' => $action,
            'category' => $category,
            'member' => $memberConnected,
        ])) {
            $this->actionManager->actionCategoryMemberManager->remove($actionCategoryMember);

            $data['action'] = $action->getReverse()->getTitle();
            $data['action_reverse'] = $action->getTitle();

            if($action->getReverse()) {
                if($actionCategoryMemberReverse = $this->actionManager->actionCategoryMemberManager->getOne([
                    'action' => $action->getReverse(),
                    'category' => $category,
                    'member' => $memberConnected,
                ])) {
                } else {
                    $actionCategoryMemberReverse = $this->actionManager->actionCategoryMemberManager->init();
                    $actionCategoryMemberReverse->setAction($action->getReverse());
                    $actionCategoryMemberReverse->setCategory($category);
                    $actionCategoryMemberReverse->setMember($memberConnected);
                    $this->actionManager->actionCategoryMemberManager->persist($actionCategoryMemberReverse);
                }
            }

        } else {
            $actionCategoryMember = $this->actionManager->actionCategoryMemberManager->init();
            $actionCategoryMember->setAction($action);
            $actionCategoryMember->setCategory($category);
            $actionCategoryMember->setMember($memberConnected);
            $this->actionManager->actionCategoryMemberManager->persist($actionCategoryMember);

            $data['action'] = $action->getTitle();
            $data['action_reverse'] = $action->getReverse()->getTitle();

            if($action->getReverse()) {
                if($actionCategoryMemberReverse = $this->actionManager->actionCategoryMemberManager->getOne([
                    'action' => $action->getReverse(),
                    'category' => $category,
                    'member' => $memberConnected,
                ])) {
                    $this->actionManager->actionCategoryMemberManager->remove($actionCategoryMemberReverse);
                }
            }
        }

        $data['entry'] = $category->toArray();
        $data['entry_entity'] = 'category';

        return new JsonResponse($data);
    }
}
