<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Entity\Connection;
use App\Entity\Member;
use App\Form\Type\PinboardType;
use App\Helper\DeviceDetectorHelper;
use App\Model\PinboardModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api', name: 'api_pinboard_', priority: 20)]
class PinboardController extends AbstractAppController
{
    #[Route('/pinboard', name: 'index', methods: ['POST'])]
    public function pinboard(Request $request): JsonResponse
    {
        $data = [];

        $pinboard = new PinboardModel();
        $form = $this->createForm(PinboardType::class, $pinboard);

        $content = $this->getContent($request);
        $form->submit($content);

        if ($form->isValid()) {
            $connection = $this->connectionManager->getOne(['type' => 'pinboard', 'member' => $this->getMember()]);

            if ($connection) {
                $connection->setToken($pinboard->getToken());
            } else {
                $extraFields = DeviceDetectorHelper::asArray($request);

                $connection = new Connection();
                $connection->setMember($this->getMember());
                $connection->setType('pinboard');
                $connection->setToken($pinboard->getToken());
                $connection->setExtraFields($extraFields);
            }
            $this->connectionManager->persist($connection);

            $data['entry'] = $connection->toArray();
            $data['entry_entity'] = 'connection';
        } else {
            $data = $this->getFormErrors($form);
            return $this->jsonResponse($data, JsonResponse::HTTP_BAD_REQUEST);
        }

        return $this->jsonResponse($data);
    }
}
