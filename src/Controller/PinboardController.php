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

        if (false === $this->getUser() instanceof Member) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $pinboard = new PinboardModel();
        $form = $this->createForm(PinboardType::class, $pinboard);

        $content = $this->getContent($request);
        $form->submit($content);

        if ($form->isValid()) {
            $connection = $this->connectionManager->getOne(['type' => 'pinboard', 'member' => $this->getUser()]);

            if ($connection) {
                $connection->setToken($pinboard->getToken());
            } else {
                $extraFields = DeviceDetectorHelper::asArray($request);

                $connection = new Connection();
                $connection->setMember($this->getUser());
                $connection->setType('pinboard');
                $connection->setToken($pinboard->getToken());
                $connection->setExtraFields($extraFields);
            }
            $this->connectionManager->persist($connection);

            $data['entry'] = $connection->toArray();
            $data['entry_entity'] = 'connection';
        } else {
            $errors = $form->getErrors(true);
            foreach ($errors as $error) {
                if (method_exists($error, 'getOrigin') && method_exists($error, 'getMessage')) {
                    $data['errors'][$error->getOrigin()->getName()] = $error->getMessage();
                }
            }
        }

        return new JsonResponse($data);
    }
}