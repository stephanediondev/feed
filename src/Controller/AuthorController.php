<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Entity\Author;
use App\Form\Type\AuthorType;
use App\Manager\ActionAuthorManager;
use App\Manager\ActionManager;
use App\Manager\AuthorManager;
use App\Manager\FeedManager;
use App\Model\QueryParameterFilterModel;
use App\Model\QueryParameterPageModel;
use App\Model\QueryParameterSortModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
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

    /**
     * @param array<mixed> $filter
     * @param array<mixed> $page
     */
    #[Route(path: '/authors', name: 'index', methods: ['GET'])]
    public function index(Request $request, #[MapQueryParameter] ?array $page, #[MapQueryParameter] ?array $filter, #[MapQueryParameter] ?string $sort): JsonResponse
    {
        $data = [];
        $included = [];

        $this->denyAccessUnlessGranted('LIST', 'author');

        $filtersModel = new QueryParameterFilterModel($filter);

        $parameters = [];

        if ($filtersModel->get('title')) {
            $parameters['title'] = $filtersModel->get('title');
        }

        if ($filtersModel->getBool('excluded')) {
            $parameters['excluded'] = true;
            $parameters['member'] = $this->getMember();
        }

        if ($filtersModel->getInt('feed')) {
            if ($feed = $this->feedManager->getOne(['id' => $filtersModel->getInt('feed')])) {
                $parameters['feed'] = $filtersModel->getInt('feed');
                $included['feed-'.$feed->getId()] = $feed->getJsonApiData();
            }
        }

        if ($filtersModel->getInt('days')) {
            $parameters['days'] = $filtersModel->getInt('days');
        }

        $sortModel = new QueryParameterSortModel($sort);

        if ($sortGet = $sortModel->get()) {
            $parameters['sortDirection'] = $sortGet['direction'];
            $parameters['sortField'] = $sortGet['field'];
        } else {
            $parameters['sortDirection'] = 'ASC';
            $parameters['sortField'] = 'aut.title';
        }

        $parameters['returnQueryBuilder'] = true;

        $pageModel = new QueryParameterPageModel($page);

        $pagination = $this->paginateAbstract($pageModel, $this->authorManager->getList($parameters));

        $data = array_merge($data, $this->jsonApi($request, $pagination, $sortModel, $filtersModel));

        $data['data'] = [];

        $ids = [];
        foreach ($pagination as $result) {
            $ids[] = $result['id'];
        }

        $results = $this->actionAuthorManager->getList(['member' => $this->getMember(), 'authors' => $ids])->getResult();
        $actions = [];
        foreach ($results as $actionAuthor) {
            $included['action-'.$actionAuthor->getAction()->getId()] = $actionAuthor->getAction()->getJsonApiData();
            $actions[$actionAuthor->getAuthor()->getId()][] = $actionAuthor->getAction()->getId();
        }

        foreach ($pagination as $result) {
            $author = $this->authorManager->getOne(['id' => $result['id']]);
            if ($author) {
                $entry = $author->getJsonApiData();

                if (true === isset($actions[$result['id']])) {
                    $entry['relationships']['actions'] = [
                        'data' => [],
                    ];
                    foreach ($actions[$result['id']] as $actionId) {
                        $entry['relationships']['actions']['data'][] = [
                            'id'=> strval($actionId),
                            'type' => 'action',
                        ];
                    }
                }

                $data['data'][] = $entry;
            }
        }

        if (0 < count($included)) {
            $data['included'] = array_values($included);
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

            $data['data'] = $author->getJsonApiData();
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

        $data['data'] = [];
        $included = [];

        $entry = $author->getJsonApiData();

        $results = $this->actionAuthorManager->getList(['member' => $this->getMember(), 'author' => $author])->getResult();
        $actions = [];
        foreach ($results as $actionAuthor) {
            $included['action-'.$actionAuthor->getAction()->getId()] = $actionAuthor->getAction()->getJsonApiData();
            $actions[$actionAuthor->getAuthor()->getId()][] = $actionAuthor->getAction()->getId();
        }

        if (true === isset($actions[$entry['id']])) {
            $entry['relationships']['actions'] = [
                'data' => [],
            ];
            foreach ($actions[$entry['id']] as $actionId) {
                $entry['relationships']['actions']['data'][] = [
                    'id'=> strval($actionId),
                    'type' => 'action',
                ];
            }
        }

        $data['data'] = $entry;

        if (0 < count($included)) {
            $data['included'] = array_values($included);
        }

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

            $data['data'] = $author->getJsonApiData();
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

        $data['data'] = $author->getJsonApiData();

        $this->authorManager->remove($author);

        return $this->jsonResponse($data);
    }

    #[Route('/author/action/exclude/{id}', name: 'action_exclude', methods: ['GET'])]
    public function actionExclude(Request $request, int $id): JsonResponse
    {
        $data = [];
        $case = 'exclude';

        $author = $this->authorManager->getOne(['id' => $id]);

        if (!$author) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $action = $this->actionManager->getOne(['title' => $case]);

        if (!$action) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('ACTION_'.strtoupper($case), $author);

        $actionAuthor = $this->actionAuthorManager->getOne([
            'action' => $action,
            'author' => $author,
            'member' => $this->getMember(),
        ]);

        $data = $this->actionAuthorManager->setAction($case, $action, $author, $actionAuthor, $this->getMember());

        return $this->jsonResponse($data);
    }

    #[Route(path: '/authors/trendy', name: 'trendy', methods: ['GET'])]
    public function trendy(Request $request): JsonResponse
    {
        $data = [];

        $this->denyAccessUnlessGranted('LIST', 'author');

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

        foreach ($data['entries'] as $k => $v) {
            $percent = ($v['count'] * 100) / $max;
            $percent = $percent - ($percent % 10);
            $percent = intval($percent) + 100;
            $data['entries'][$k]['percent'] = $percent;
        }

        return $this->jsonResponse($data);
    }
}
