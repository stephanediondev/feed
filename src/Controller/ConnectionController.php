<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api', name: 'api_connections_')]
class ConnectionController extends AbstractAppController
{
    public function create(Request $request)
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    #[Route('/connection/{id}', name: 'read', methods: ['PUT'])]
    public function read(Request $request, $id): JsonResponse
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $connection = $this->memberManager->connectionManager->getOne(['id' => $id, 'member' => $memberConnected]);

        if (!$connection) {
            return new JsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $data['entry'] = $connection->toArray();
        $data['entry_entity'] = 'connection';

        return new JsonResponse($data);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $connection = $this->memberManager->connectionManager->getOne(['id' => $id, 'member' => $memberConnected]);

        if (!$connection) {
            return new JsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $connection->setIp($request->getClientIp());
        $connection->setAgent($request->server->get('HTTP_USER_AGENT'));
        $connection_id = $this->memberManager->connectionManager->persist($connection);

        $data['entry'] = $connection->toArray();
        $data['entry_entity'] = 'connection';

        //$data['applicationServerKey'] = $this->applicationServerKey;

        return new JsonResponse($data);
    }

    #[Route('/connection/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, $id): JsonResponse
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $connection = $this->memberManager->connectionManager->getOne(['id' => $id, 'member' => $memberConnected]);

        if (!$connection) {
            return new JsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $data['entry'] = $connection->toArray();
        $data['entry_entity'] = 'connection';

        $this->memberManager->connectionManager->remove($connection);

        return new JsonResponse($data);
    }
}
