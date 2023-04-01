<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Entity\Connection;
use App\Form\Type\PinboardType;
use App\Model\PinboardModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api', name: 'api_pinboard_', priority: 20)]
class PinboardController extends AbstractAppController
{
    public function pinboard(Request $request): JsonResponse
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $pinboard = new PinboardModel();
        $form = $this->createForm(PinboardType::class, $pinboard);

        $content = $this->getContent($request);
        $form->submit($content);

        if ($form->isValid()) {
            $connection = $this->connectionManager->getOne(['type' => 'pinboard', 'member' => $memberConnected]);

            if ($connection) {
                $connection->setToken($pinboard->getToken());
            } else {
                $connection = new Connection();
                $connection->setMember($memberConnected);
                $connection->setType('pinboard');
                $connection->setToken($pinboard->getToken());
                $connection->setIp($request->getClientIp());
                $connection->setAgent($request->server->get('HTTP_USER_AGENT'));
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
