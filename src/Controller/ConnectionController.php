<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api', name: 'api_connections_', priority: 15)]
class ConnectionController extends AbstractAppController
{
    #[Route('/connection/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, int $id): JsonResponse
    {
        $data = [];

        $connection = $this->connectionManager->getOne(['id' => $id, 'member' => $this->getUser()]);

        if (!$connection) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $data['entry'] = $connection->toArray();
        $data['entry_entity'] = 'connection';

        $this->connectionManager->remove($connection);

        return $this->jsonResponse($data);
    }
}
