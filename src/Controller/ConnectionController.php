<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Controller\AbstractAppController;

#[Route(path: '/api', name: 'api_connections_')]
class ConnectionController extends AbstractAppController
{
    public function create(Request $request)
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    #[Route('/connection/{id}', name: 'read', methods: ['PUT'])]
    public function read(Request $request, $id)
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $connection = $this->memberManager->connectionManager->getOne(['id' => $id, 'member' => $memberConnected]);

        if (!$connection) {
            return new JsonResponse($data, 404);
        }

        $data['entry'] = $connection->toArray();
        $data['entry_entity'] = 'connection';

        return new JsonResponse($data);
    }

    public function update(Request $request, $id)
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $connection = $this->memberManager->connectionManager->getOne(['id' => $id, 'member' => $memberConnected]);

        if (!$connection) {
            return new JsonResponse($data, 404);
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
    public function delete(Request $request, $id)
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $connection = $this->memberManager->connectionManager->getOne(['id' => $id, 'member' => $memberConnected]);

        if (!$connection) {
            return new JsonResponse($data, 404);
        }

        $data['entry'] = $connection->toArray();
        $data['entry_entity'] = 'connection';

        $this->memberManager->connectionManager->remove($connection);

        return new JsonResponse($data);
    }
}
