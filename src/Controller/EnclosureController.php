<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Manager\EnclosureManager;
use App\Manager\ItemManager;
use App\Model\QueryParameterFilterModel;
use App\Model\QueryParameterSortModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api', name: 'api_enclosures_', priority: 15)]
class EnclosureController extends AbstractAppController
{
    private EnclosureManager $enclosureManager;
    private ItemManager $itemManager;

    public function __construct(EnclosureManager $enclosureManager, ItemManager $itemManager)
    {
        $this->enclosureManager = $enclosureManager;
        $this->itemManager = $itemManager;
    }

    #[Route(path: '/enclosures', name: 'index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $data = [];

        $this->denyAccessUnlessGranted('LIST', 'enclosure');

        $filters = new QueryParameterFilterModel($request->query->all('filter'));

        $parameters = [];

        if ($filters->getInt('item')) {
            if ($item = $this->itemManager->getOne(['id' => $filters->getInt('item')])) {
                $parameters['item'] = $filters->getInt('item');
                $data['entry'] = $item->toArray();
                $data['entry_entity'] = 'item';
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
            $parameters['sortField'] = 'itm.date';
        }

        $parameters['returnQueryBuilder'] = true;

        $pagination = $this->paginateAbstract($this->enclosureManager->getList($parameters));

        $data['entries_entity'] = 'enclosure';
        $data = array_merge($data, $this->jsonApi($pagination, 'api_enclosures_index', $request->query->get('sort'), $filters));

        $data['entries'] = [];

        foreach ($pagination as $result) {
            $entry = $result->toArray();
            $data['entries'][] = $entry;
        }

        return $this->jsonResponse($data);
    }

    #[Route('/enclosure/{id}', name: 'read', methods: ['GET'])]
    public function read(Request $request, int $id): JsonResponse
    {
        $data = [];

        $enclosure = $this->enclosureManager->getOne(['id' => $id]);

        if (!$enclosure) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('READ', $enclosure);

        $data['entry'] = $enclosure->toArray();
        $data['entry_entity'] = 'enclosure';

        return $this->jsonResponse($data);
    }

    #[Route('/enclosure/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, int $id): JsonResponse
    {
        $data = [];

        $enclosure = $this->enclosureManager->getOne(['id' => $id]);

        if (!$enclosure) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('DELETE', $enclosure);

        $data['entry'] = $enclosure->toArray();
        $data['entry_entity'] = 'enclosure';

        $this->enclosureManager->remove($enclosure);

        return $this->jsonResponse($data);
    }
}
