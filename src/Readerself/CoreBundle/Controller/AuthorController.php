<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Readerself\CoreBundle\Controller\AbstractController;

use Readerself\CoreBundle\Form\Type\AuthorType;

class AuthorController extends AbstractController
{
    /**
     * Retrieve all authors.
     *
     * @ApiDoc(
     *     section="Author",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     *     parameters={
     *         {"name"="sortField", "dataType"="string", "required"=false, "format"="""title"" or ""date_created"", default ""title""", "description"=""},
     *         {"name"="sortDirection", "dataType"="string", "required"=false, "format"="""ASC"" or ""DESC"", default ""DESC""", "description"=""},
     *         {"name"="page", "dataType"="integer", "required"=false, "format"="default ""1""", "description"="page number"},
     *         {"name"="perPage", "dataType"="integer", "required"=false, "format"="default ""100""", "description"="authors per page"},
     *         {"name"="recent", "dataType"="integer", "required"=false, "format"="1 or 0", "description"="recent authors"},
     *         {"name"="excluded", "dataType"="integer", "required"=false, "format"="1 or 0", "description"="excluded authors"},
     *         {"name"="feed", "dataType"="integer", "required"=false, "format"="feed ID", "description"="authors by feed"},
     *     },
     * )
     */
    public function indexAction(Request $request)
    {
        $data = [];
        $memberConnected = $this->validateToken($request);

        $parameters = [];

        if($request->query->get('trendy')) {
            $parameters['trendy'] = true;

            if($memberConnected) {
                $parameters['member'] = $memberConnected;
            }

            $results = $this->authorManager->getList($parameters);

            $data['entries'] = [];

            $max = false;
            foreach($results as $row) {
                if(!$max) {
                    $max = $row['count'];
                }
                $data['entries'][$row['ref']] = ['count' => $row['count'], 'id' => $row['id']];
            }
            //ksort($data['entries']);

            foreach($data['entries'] as $k => $v) {
                $percent = ($v['count'] * 100) / $max;
                $percent = $percent - ($percent % 10);
                $percent = intval($percent) + 100;
                $data['entries'][$k]['percent'] = $percent;
            }

        } else {
            if($request->query->get('excluded')) {
                if(!$memberConnected) {
                    return new JsonResponse($data, 403);
                }
                $parameters['excluded'] = true;
                $parameters['member'] = $memberConnected;
            }

            if($request->query->get('feed')) {
                $parameters['feed'] = (int) $request->query->get('feed');
                $data['entry'] = $this->feedManager->getOne(['id' => (int) $request->query->get('feed')])->toArray();
                $data['entry_entity'] = 'feed';
            }

            if($request->query->get('days')) {
                $parameters['days'] = (int) $request->query->get('days');
            }

            $fields = ['title' => 'aut.title', 'date_created' => 'aut.dateCreated'];
            if($request->query->get('sortField') && array_key_exists($request->query->get('sortField'), $fields)) {
                $parameters['sortField'] = $fields[$request->query->get('sortField')];
            } else {
                $parameters['sortField'] = 'aut.title';
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
                $this->authorManager->getList($parameters),
                $page = $request->query->getInt('page', 1),
                $request->query->getInt('perPage', 100)
            );

            $data['entries_entity'] = 'author';
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
                $author = $this->authorManager->getOne(['id' => $result['id']]);
                $actions = $this->get('readerself_core_manager_action')->actionAuthorMemberManager->getList(['member' => $memberConnected, 'author' => $author])->getResult();

                $data['entries'][$index] = $author->toArray();
                foreach($actions as $action) {
                    $data['entries'][$index][$action->getAction()->getTitle()] = true;
                }
                $index++;
            }
        }

        return new JsonResponse($data);
    }

    /**
     * Create an author.
     *
     * @ApiDoc(
     *     section="Author",
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

        $form = $this->createForm(AuthorType::class, $this->authorManager->init());

        $form->submit($request->request->all(), false);

        if($form->isValid()) {
            $author_id = $this->authorManager->persist($form->getData());

            $data['entry'] = $this->authorManager->getOne(['id' => $author_id])->toArray();
            $data['entry_entity'] = 'author';

        } else {
            $errors = $form->getErrors(true);
            foreach($errors as $error) {
                $data['errors'][$error->getOrigin()->getName()] = $error->getMessage();
            }
        }

        return new JsonResponse($data);
    }

    /**
     * Retrieve an author.
     *
     * @ApiDoc(
     *     section="Author",
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

        $author = $this->authorManager->getOne(['id' => $id]);

        if(!$author) {
            return new JsonResponse($data, 404);
        }

        $data['entry'] = $author->toArray();
        $data['entry_entity'] = 'author';

        return new JsonResponse($data);
    }

    /**
     * Update an author.
     *
     * @ApiDoc(
     *     section="Author",
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

        $author = $this->authorManager->getOne(['id' => $id]);

        if(!$author) {
            return new JsonResponse($data, 404);
        }

        $data['entry'] = $author->toArray();
        $data['entry_entity'] = 'author';

        return new JsonResponse($data);
    }

    /**
     * Delete an author.
     *
     * @ApiDoc(
     *     section="Author",
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

        $author = $this->authorManager->getOne(['id' => $id]);

        if(!$author) {
            return new JsonResponse($data, 404);
        }

        $data['entry'] = $author->toArray();
        $data['entry_entity'] = 'author';

        $this->authorManager->remove($author);

        return new JsonResponse($data);
    }

    /**
     * Set "exclude" action / Remove "exclude" action.
     *
     * @ApiDoc(
     *     section="Author",
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

        $author = $this->authorManager->getOne(['id' => $id]);

        if(!$author) {
            return new JsonResponse($data, 404);
        }

        $action = $this->actionManager->getOne(['title' => $case]);

        if($actionAuthorMember = $this->actionManager->actionAuthorMemberManager->getOne([
            'action' => $action,
            'author' => $author,
            'member' => $memberConnected,
        ])) {
            $this->actionManager->actionAuthorMemberManager->remove($actionAuthorMember);

            $data['action'] = $action->getReverse()->getTitle();
            $data['action_reverse'] = $action->getTitle();

            if($action->getReverse()) {
                if($actionAuthorMemberReverse = $this->actionManager->actionAuthorMemberManager->getOne([
                    'action' => $action->getReverse(),
                    'author' => $author,
                    'member' => $memberConnected,
                ])) {
                } else {
                    $actionAuthorMemberReverse = $this->actionManager->actionAuthorMemberManager->init();
                    $actionAuthorMemberReverse->setAction($action->getReverse());
                    $actionAuthorMemberReverse->setAuthor($author);
                    $actionAuthorMemberReverse->setMember($memberConnected);
                    $this->actionManager->actionAuthorMemberManager->persist($actionAuthorMemberReverse);
                }
            }

        } else {
            $actionAuthorMember = $this->actionManager->actionAuthorMemberManager->init();
            $actionAuthorMember->setAction($action);
            $actionAuthorMember->setAuthor($author);
            $actionAuthorMember->setMember($memberConnected);
            $this->actionManager->actionAuthorMemberManager->persist($actionAuthorMember);

            $data['action'] = $action->getTitle();
            $data['action_reverse'] = $action->getReverse()->getTitle();

            if($action->getReverse()) {
                if($actionAuthorMemberReverse = $this->actionManager->actionAuthorMemberManager->getOne([
                    'action' => $action->getReverse(),
                    'author' => $author,
                    'member' => $memberConnected,
                ])) {
                    $this->actionManager->actionAuthorMemberManager->remove($actionAuthorMemberReverse);
                }
            }
        }

        $data['entry'] = $author->toArray();
        $data['entry_entity'] = 'author';

        return new JsonResponse($data);
    }
}
