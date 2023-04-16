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
use App\Model\QueryParameterFilterModel;
use App\Model\QueryParameterSortModel;
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

        $this->denyAccessUnlessGranted('LIST', 'author');

        $filters = new QueryParameterFilterModel($request->query->all('filter'));

        $parameters = [];

        if ($filters->getBool('excluded')) {
            $parameters['excluded'] = true;
            $parameters['member'] = $this->getMember();
        }

        if ($filters->getInt('feed')) {
            if ($feed = $this->feedManager->getOne(['id' => $filters->getInt('feed')])) {
                $parameters['feed'] = $filters->getInt('feed');
                $data['entry'] = $feed->toArray();
                $data['entry_entity'] = 'feed';
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
            $parameters['sortDirection'] = 'ASC';
            $parameters['sortField'] = 'aut.title';
        }

        $parameters['returnQueryBuilder'] = true;

        $pagination = $this->paginateAbstract($this->authorManager->getList($parameters));

        $data['entries_entity'] = 'author';
        $data = array_merge($data, $this->getEntriesInfo($pagination));

        $data['entries'] = [];

        $ids = [];
        foreach ($pagination as $result) {
            $ids[] = $result['id'];
        }

        $results = $this->actionAuthorManager->getList(['member' => $this->getMember(), 'authors' => $ids])->getResult();
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

        return $this->jsonResponse($data);
    }

    #[Route(path: '/authors', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = [];

        $this->denyAccessUnlessGranted('CREATE', 'author');

        $author = new Author();
        $form = $this->createForm(AuthorType::class, $author);

        $content = $this->getContent($request);
        $form->submit($content);

        if ($form->isValid()) {
            $this->authorManager->persist($form->getData());

            $data['entry'] = $author->toArray();
            $data['entry_entity'] = 'author';
        } else {
            $data = $this->getFormErrors($form);
            return $this->jsonResponse($data, JsonResponse::HTTP_BAD_REQUEST);
        }

        return $this->jsonResponse($data, JsonResponse::HTTP_CREATED);
    }

    #[Route('/author/{id}', name: 'read', methods: ['GET'])]
    public function read(Request $request, int $id): JsonResponse
    {
        $data = [];

        $author = $this->authorManager->getOne(['id' => $id]);

        if (!$author) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('READ', $author);

        $data['entry'] = $author->toArray();
        $data['entry_entity'] = 'author';

        return $this->jsonResponse($data);
    }

    #[Route('/author/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $data = [];

        $author = $this->authorManager->getOne(['id' => $id]);

        if (!$author) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('UPDATE', $author);

        $form = $this->createForm(AuthorType::class, $author);

        $content = $this->getContent($request);
        $form->submit($content);

        if ($form->isValid()) {
            $this->authorManager->persist($form->getData());

            $data['entry'] = $author->toArray();
            $data['entry_entity'] = 'author';
        } else {
            $data = $this->getFormErrors($form);
            return $this->jsonResponse($data, JsonResponse::HTTP_BAD_REQUEST);
        }

        return $this->jsonResponse($data);
    }

    #[Route('/author/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, int $id): JsonResponse
    {
        $data = [];

        $author = $this->authorManager->getOne(['id' => $id]);

        if (!$author) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('DELETE', $author);

        $data['entry'] = $author->toArray();
        $data['entry_entity'] = 'author';

        $this->authorManager->remove($author);

        return $this->jsonResponse($data);
    }

    #[Route('/author/action/exclude/{id}', name: 'action_exclude', methods: ['GET'])]
    public function actionExclude(Request $request, int $id): JsonResponse
    {
        return $this->setAction('exclude', $request, $id);
    }

    private function setAction(string $case, Request $request, int $id): JsonResponse
    {
        $data = [];

        $author = $this->authorManager->getOne(['id' => $id]);

        if (!$author) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $action = $this->actionManager->getOne(['title' => $case]);

        if (!$action) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('ACTION_'.strtoupper($case), $author);

        if ($actionAuthor = $this->actionAuthorManager->getOne([
            'action' => $action,
            'author' => $author,
            'member' => $this->getMember(),
        ])) {
            $this->actionAuthorManager->remove($actionAuthor);

            $data['action'] = $action->getReverse() ? $action->getReverse()->getTitle() : null;
            $data['action_reverse'] = $action->getTitle();

            if ($action->getReverse()) {
                if ($actionAuthorReverse = $this->actionAuthorManager->getOne([
                    'action' => $action->getReverse(),
                    'author' => $author,
                    'member' => $this->getMember(),
                ])) {
                } else {
                    $actionAuthorReverse = new ActionAuthor();
                    $actionAuthorReverse->setAction($action->getReverse());
                    $actionAuthorReverse->setAuthor($author);
                    $actionAuthorReverse->setMember($this->getMember());
                    $this->actionAuthorManager->persist($actionAuthorReverse);
                }
            }
        } else {
            $actionAuthor = new ActionAuthor();
            $actionAuthor->setAction($action);
            $actionAuthor->setAuthor($author);
            $actionAuthor->setMember($this->getMember());
            $this->actionAuthorManager->persist($actionAuthor);

            $data['action'] = $action->getTitle();
            $data['action_reverse'] = $action->getReverse() ? $action->getReverse()->getTitle() : null;

            if ($action->getReverse()) {
                if ($actionAuthorReverse = $this->actionAuthorManager->getOne([
                    'action' => $action->getReverse(),
                    'author' => $author,
                    'member' => $this->getMember(),
                ])) {
                    $this->actionAuthorManager->remove($actionAuthorReverse);
                }
            }
        }

        $data['entry'] = $author->toArray();
        $data['entry_entity'] = 'author';

        return $this->jsonResponse($data);
    }

    #[Route(path: '/authors/trendy', name: 'trendy', methods: ['GET'])]
    public function trendy(Request $request): JsonResponse
    {
        $data = [];

        $this->denyAccessUnlessGranted('LIST', 'author');

        $filters = new QueryParameterFilterModel($request->query->all('filter'));

        $parameters = [];

        $parameters['trendy'] = true;

        if ($this->getMember()) {
            $parameters['member'] = $this->getMember();
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

        return $this->jsonResponse($data);
    }
}
