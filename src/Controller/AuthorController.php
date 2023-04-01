<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Entity\ActionAuthor;
use App\Entity\Author;
use App\Entity\Member;
use App\Form\Type\AuthorType;
use App\Manager\ActionAuthorManager;
use App\Manager\ActionManager;
use App\Manager\AuthorManager;
use App\Manager\FeedManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api', name: 'api_authors_', priority: 15)]
class AuthorController extends AbstractAppController
{
    private ActionManager $actionManager;
    private ActionAuthorManager $actionAuthorManager;
    private AuthorManager $authorManager;
    private FeedManager $feedManager;

    public function __construct(ActionManager $actionManager, ActionAuthorManager $actionAuthorManager, AuthorManager $authorManager, FeedManager $feedManager)
    {
        $this->actionManager = $actionManager;
        $this->actionAuthorManager = $actionAuthorManager;
        $this->authorManager = $authorManager;
        $this->feedManager = $feedManager;
    }

    #[Route(path: '/authors', name: 'index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $data = [];

        $parameters = [];

        if ($request->query->get('trendy')) {
            $parameters['trendy'] = true;

            if ($this->getUser()) {
                $parameters['member'] = $this->getUser();
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
                $parameters['excluded'] = true;
                $parameters['member'] = $this->getUser();
            }

            if ($request->query->get('feed')) {
                if ($feed = $this->feedManager->getOne(['id' => (int) $request->query->get('feed')])) {
                    $parameters['feed'] = (int) $request->query->get('feed');
                    $data['entry'] = $feed->toArray();
                    $data['entry_entity'] = 'feed';
                }
            }

            if ($request->query->get('days')) {
                $parameters['days'] = (int) $request->query->get('days');
            }

            $fields = ['title' => 'aut.title', 'date_created' => 'aut.dateCreated'];
            if ($request->query->get('sortField') && array_key_exists(strval($request->query->get('sortField')), $fields)) {
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

            $ids = [];
            foreach ($pagination as $result) {
                $ids[] = $result['id'];
            }

            $results = $this->actionAuthorManager->getList(['member' => $this->getUser(), 'authors' => $ids])->getResult();
            $actions = [];
            foreach ($results as $actionAuthor) {
                $actions[$actionAuthor->getAuthor()->getId()][] = $actionAuthor;
            }

            foreach ($pagination as $result) {
                $author = $this->authorManager->getOne(['id' => $result['id']]);
                if ($author) {
                    $entry = $author->toArray();

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

    #[Route(path: '/aurhors', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = [];

        $author = new Author();
        $form = $this->createForm(AuthorType::class, $author);

        $content = $this->getContent($request);
        $form->submit($content);

        if ($form->isValid()) {
            $this->authorManager->persist($form->getData());

            $data['entry'] = $author->toArray();
            $data['entry_entity'] = 'author';
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

    #[Route('/author/{id}', name: 'read', methods: ['GET'])]
    public function read(Request $request, int $id): JsonResponse
    {
        $data = [];

        $author = $this->authorManager->getOne(['id' => $id]);

        if (!$author) {
            return new JsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $data['entry'] = $author->toArray();
        $data['entry_entity'] = 'author';

        return new JsonResponse($data);
    }

    #[Route('/author/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $data = [];

        $author = $this->authorManager->getOne(['id' => $id]);

        if (!$author) {
            return new JsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $data['entry'] = $author->toArray();
        $data['entry_entity'] = 'author';

        return new JsonResponse($data);
    }

    #[Route('/author/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, int $id): JsonResponse
    {
        $data = [];

        if (false === $this->isGranted('ROLE_ADMIN')) {
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
    public function actionExclude(Request $request, int $id): JsonResponse
    {
        return $this->setAction('exclude', $request, $id);
    }

    private function setAction(string $case, Request $request, int $id): JsonResponse
    {
        $data = [];

        if (false === $this->getUser() instanceof Member) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $author = $this->authorManager->getOne(['id' => $id]);

        if (!$author) {
            return new JsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $action = $this->actionManager->getOne(['title' => $case]);

        if (!$action) {
            return new JsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        if ($actionAuthor = $this->actionAuthorManager->getOne([
            'action' => $action,
            'author' => $author,
            'member' => $this->getUser(),
        ])) {
            $this->actionAuthorManager->remove($actionAuthor);

            $data['action'] = $action->getReverse() ? $action->getReverse()->getTitle() : null;
            $data['action_reverse'] = $action->getTitle();

            if ($action->getReverse()) {
                if ($actionAuthorReverse = $this->actionAuthorManager->getOne([
                    'action' => $action->getReverse(),
                    'author' => $author,
                    'member' => $this->getUser(),
                ])) {
                } else {
                    $actionAuthorReverse = new ActionAuthor();
                    $actionAuthorReverse->setAction($action->getReverse());
                    $actionAuthorReverse->setAuthor($author);
                    $actionAuthorReverse->setMember($this->getUser());
                    $this->actionAuthorManager->persist($actionAuthorReverse);
                }
            }
        } else {
            $actionAuthor = new ActionAuthor();
            $actionAuthor->setAction($action);
            $actionAuthor->setAuthor($author);
            $actionAuthor->setMember($this->getUser());
            $this->actionAuthorManager->persist($actionAuthor);

            $data['action'] = $action->getTitle();
            $data['action_reverse'] = $action->getReverse() ? $action->getReverse()->getTitle() : null;

            if ($action->getReverse()) {
                if ($actionAuthorReverse = $this->actionAuthorManager->getOne([
                    'action' => $action->getReverse(),
                    'author' => $author,
                    'member' => $this->getUser(),
                ])) {
                    $this->actionAuthorManager->remove($actionAuthorReverse);
                }
            }
        }

        $data['entry'] = $author->toArray();
        $data['entry_entity'] = 'author';

        return new JsonResponse($data);
    }
}
