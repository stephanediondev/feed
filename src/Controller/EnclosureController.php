<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Manager\EnclosureManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api', name: 'api_enclosures_', priority: 15)]
class EnclosureController extends AbstractAppController
{
    private EnclosureManager $enclosureManager;

    public function __construct(EnclosureManager $enclosureManager)
    {
        $this->enclosureManager = $enclosureManager;
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
