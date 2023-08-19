<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Manager\MemberManager;
use App\Model\QueryParameterFilterModel;
use App\Model\QueryParameterPageModel;
use App\Model\QueryParameterSortModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api', name: 'api_connections_', priority: 15)]
class ConnectionController extends AbstractAppController
{
    private MemberManager $memberManager;

    public function __construct(MemberManager $memberManager)
    {
        $this->memberManager = $memberManager;
    }

    /**
     * @param array<mixed> $filter
     * @param array<mixed> $page
     */
    #[Route(path: '/connections', name: 'index', methods: ['GET'])]
    public function index(Request $request, #[MapQueryParameter] ?array $page, #[MapQueryParameter] ?array $filter, #[MapQueryParameter] ?string $sort): JsonResponse
    {
        $data = [];

        $this->denyAccessUnlessGranted('LIST', 'connection');

        $filtersModel = new QueryParameterFilterModel($filter);

        $parameters = [];

        if ($filtersModel->getInt('member')) {
            if ($member = $this->memberManager->getOne(['id' => $filtersModel->getInt('member')])) {
                $parameters['member'] = $filtersModel->getInt('member');
                $data['entry'] = $member->toArray();
                $data['entry_entity'] = 'member';
            }
        }

        if ($filtersModel->get('type')) {
            $parameters['type'] = $filtersModel->get('type');
        }

        if ($filtersModel->getInt('days')) {
            $parameters['days'] = $filtersModel->getInt('days');
        }

        $sortModel = new QueryParameterSortModel($sort);

        if ($sortGet = $sortModel->get()) {
            $parameters['sortDirection'] = $sortGet['direction'];
            $parameters['sortField'] = $sortGet['field'];
        } else {
            $parameters['sortDirection'] = 'DESC';
            $parameters['sortField'] = 'cnt.dateModified';
        }

        $parameters['returnQueryBuilder'] = true;

        $pageModel = new QueryParameterPageModel($page);

        $pagination = $this->paginateAbstract($pageModel, $this->connectionManager->getList($parameters));

        $data['entries_entity'] = 'connection';
        $data = array_merge($data, $this->jsonApi($request, $pagination, $sortModel, $filtersModel));

        $data['entries'] = [];

        foreach ($pagination as $result) {
            $entry = $result->toArray();
            $data['entries'][] = $entry;
        }

        return $this->jsonResponse($data);
    }

    #[Route('/connection/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, int $id): JsonResponse
    {
        $data = [];

        $connection = $this->connectionManager->getOne(['id' => $id, 'member' => $this->getMember()]);

        if (!$connection) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('DELETE', $connection);

        $data['entry'] = $connection->toArray();
        $data['entry_entity'] = 'connection';

        $this->connectionManager->remove($connection);

        return $this->jsonResponse($data);
    }
}
