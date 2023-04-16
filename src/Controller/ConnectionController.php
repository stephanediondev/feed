<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Manager\MemberManager;
use App\Model\QueryParameterFilterModel;
use App\Model\QueryParameterSortModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api', name: 'api_connections_', priority: 15)]
class ConnectionController extends AbstractAppController
{
    private MemberManager $memberManager;

    public function __construct(MemberManager $memberManager)
    {
        $this->memberManager = $memberManager;
    }

    #[Route(path: '/connections', name: 'index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $data = [];

        $this->denyAccessUnlessGranted('LIST', 'connection');

        $filters = new QueryParameterFilterModel($request->query->all('filter'));

        $parameters = [];

        if ($filters->getInt('member')) {
            if ($member = $this->memberManager->getOne(['id' => $filters->getInt('member')])) {
                $parameters['member'] = $filters->getInt('member');
                $data['entry'] = $member->toArray();
                $data['entry_entity'] = 'member';
            }
        }

        if ($filters->get('type')) {
            $parameters['type'] = $filters->get('type');
        }

        if ($filters->getInt('days')) {
            $parameters['days'] = $filters->getInt('days');
        }

        $sort = (new QueryParameterSortModel($request->query->get('sort')))->get();

        if ($sort) {
            $parameters['sortDirection'] = $sort['direction'];
            $parameters['sortField'] = $sort['field'];
        } else {
            $parameters['sortDirection'] = 'DESC';
            $parameters['sortField'] = 'cnt.dateModified';
        }

        $parameters['returnQueryBuilder'] = true;

        $pagination = $this->paginateAbstract($this->connectionManager->getList($parameters));

        $data['entries_entity'] = 'connection';
        $data['entries_total'] = $pagination->getTotalItemCount();
        $data['entries_pages'] = $pages = ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage());
        $data['entries_page_current'] = $pagination->getCurrentPageNumber();
        $pagePrevious = $pagination->getCurrentPageNumber() - 1;
        if ($pagePrevious >= 1) {
            $data['entries_page_previous'] = $pagePrevious;
        }
        $pageNext = $pagination->getCurrentPageNumber() + 1;
        if ($pageNext <= $pages) {
            $data['entries_page_next'] = $pageNext;
        }

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
