<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api', name: 'api_connections_')]
class ConnectionController extends AbstractAppController
{
    #[Route('/connection/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, int $id): JsonResponse
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $connection = $this->connectionManager->getOne(['id' => $id, 'member' => $memberConnected]);

        if (!$connection) {
            return new JsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $data['entry'] = $connection->toArray();
        $data['entry_entity'] = 'connection';

        $this->connectionManager->remove($connection);

        return new JsonResponse($data);
    }
}
