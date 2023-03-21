<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Form\Type\AuthorType;
use App\Manager\ActionManager;
use App\Manager\AuthorManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api', name: 'api_authors_')]
class AuthorController extends AbstractAppController
{
    private ActionManager $actionManager;
    private AuthorManager $authorManager;

    public function __construct(ActionManager $actionManager, AuthorManager $authorManager)
    {
        $this->actionManager = $actionManager;
        $this->authorManager = $authorManager;
    }

    #[Route(path: '/authors', name: 'index', methods: ['GET'])]
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

            $results = $this->authorManager->getList($parameters);

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

            if ($request->query->get('feed')) {
                $parameters['feed'] = (int) $request->query->get('feed');
                $data['entry'] = $this->authorManager->getOne(['id' => (int) $request->query->get('feed')])->toArray();
                $data['entry_entity'] = 'feed';
            }

            if ($request->query->get('days')) {
                $parameters['days'] = (int) $request->query->get('days');
            }

            $fields = ['title' => 'aut.title', 'date_created' => 'aut.dateCreated'];
            if ($request->query->get('sortField') && array_key_exists($request->query->get('sortField'), $fields)) {
                $parameters['sortField'] = $fields[$request->query->get('sortField')];
            } else {
                $parameters['sortField'] = 'aut.title';
            }

            $directions = ['ASC', 'DESC'];
            if ($request->query->get('sortDirection') && in_array($request->query->get('sortDirection'), $directions)) {
                $parameters['sortDirection'] = $request->query->get('sortDirection');
            } else {
                $parameters['sortDirection'] = 'ASC';
            }

            $parameters['returnQueryBuilder'] = true;

            $pagination = $this->paginateAbstract($this->authorManager->getList($parameters), $page = intval($request->query->getInt('page', 1)), intval($request->query->getInt('perPage', 20)));

            $data['entries_entity'] = 'author';
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

            $index = 0;
            foreach ($pagination as $result) {
                $author = $this->authorManager->getOne(['id' => $result['id']]);
                $actions = $this->actionManager->actionAuthorManager->getList(['member' => $memberConnected, 'author' => $author])->getResult();

                $data['entries'][$index] = $author->toArray();
                foreach ($actions as $action) {
                    $data['entries'][$index][$action->getAction()->getTitle()] = true;
                }
                $index++;
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

        $form = $this->createForm(AuthorType::class, $this->authorManager->init());

        $form->submit($request->request->all(), false);

        if ($form->isValid()) {
            $author_id = $this->authorManager->persist($form->getData());

            $data['entry'] = $this->authorManager->getOne(['id' => $author_id])->toArray();
            $data['entry_entity'] = 'author';
        } else {
            $errors = $form->getErrors(true);
            foreach ($errors as $error) {
                $data['errors'][$error->getOrigin()->getName()] = $error->getMessage();
            }
        }

        return new JsonResponse($data);
    }

    #[Route('/author/{id}', name: 'read', methods: ['GET'])]
    public function read(Request $request, $id): JsonResponse
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            //return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $author = $this->authorManager->getOne(['id' => $id]);

        if (!$author) {
            return new JsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $data['entry'] = $author->toArray();
        $data['entry_entity'] = 'author';

        return new JsonResponse($data);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $author = $this->authorManager->getOne(['id' => $id]);

        if (!$author) {
            return new JsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $data['entry'] = $author->toArray();
        $data['entry_entity'] = 'author';

        return new JsonResponse($data);
    }

    public function delete(Request $request, $id): JsonResponse
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        if (!$memberConnected->getAdministrator()) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $author = $this->authorManager->getOne(['id' => $id]);

        if (!$author) {
            return new JsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $data['entry'] = $author->toArray();
        $data['entry_entity'] = 'author';

        $this->authorManager->remove($author);

        return new JsonResponse($data);
    }

    #[Route('/author/action/exclude/{id}', name: 'action_exclude', methods: ['GET'])]
    public function actionExclude(Request $request, $id): JsonResponse
    {
        return $this->setAction('exclude', $request, $id);
    }

    private function setAction($case, Request $request, $id): JsonResponse
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $author = $this->authorManager->getOne(['id' => $id]);

        if (!$author) {
            return new JsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $action = $this->actionManager->getOne(['title' => $case]);

        if ($actionAuthor = $this->actionManager->actionAuthorManager->getOne([
            'action' => $action,
            'author' => $author,
            'member' => $memberConnected,
        ])) {
            $this->actionManager->actionAuthorManager->remove($actionAuthor);

            $data['action'] = $action->getReverse()->getTitle();
            $data['action_reverse'] = $action->getTitle();

            if ($action->getReverse()) {
                if ($actionAuthorReverse = $this->actionManager->actionAuthorManager->getOne([
                    'action' => $action->getReverse(),
                    'author' => $author,
                    'member' => $memberConnected,
                ])) {
                } else {
                    $actionAuthorReverse = $this->actionManager->actionAuthorManager->init();
                    $actionAuthorReverse->setAction($action->getReverse());
                    $actionAuthorReverse->setAuthor($author);
                    $actionAuthorReverse->setMember($memberConnected);
                    $this->actionManager->actionAuthorManager->persist($actionAuthorReverse);
                }
            }
        } else {
            $actionAuthor = $this->actionManager->actionAuthorManager->init();
            $actionAuthor->setAction($action);
            $actionAuthor->setAuthor($author);
            $actionAuthor->setMember($memberConnected);
            $this->actionManager->actionAuthorManager->persist($actionAuthor);

            $data['action'] = $action->getTitle();
            $data['action_reverse'] = $action->getReverse()->getTitle();

            if ($action->getReverse()) {
                if ($actionAuthorReverse = $this->actionManager->actionAuthorManager->getOne([
                    'action' => $action->getReverse(),
                    'author' => $author,
                    'member' => $memberConnected,
                ])) {
                    $this->actionManager->actionAuthorManager->remove($actionAuthorReverse);
                }
            }
        }

        $data['entry'] = $author->toArray();
        $data['entry_entity'] = 'author';

        return new JsonResponse($data);
    }
}
