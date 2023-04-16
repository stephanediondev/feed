<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Entity\Connection;
use App\Form\Type\PushType;
use App\Helper\DeviceDetectorHelper;
use App\Helper\MaxmindHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api', name: 'api_push_', priority: 15)]
class PushController extends AbstractAppController
{
    private bool $maxmindEnabled;

    public function __construct(bool $maxmindEnabled)
    {
        $this->maxmindEnabled = $maxmindEnabled;
    }

    #[Route(path: '/push/create', name: 'create', methods: ['POST'], priority: 20)]
    public function create(Request $request): JsonResponse
    {
        $data = [];

        $connection = new Connection();
        $form = $this->createForm(PushType::class, $connection);

        $content = $this->getContent($request);
        $form->submit($content);

        if ($form->isValid()) {
            $extraFields = DeviceDetectorHelper::asArray($request);

            if ($extraFields['ip'] && '127.0.0.1' != $extraFields['ip'] && true === $this->maxmindEnabled) {
                $data = MaxmindHelper::get($extraFields['ip']);
                $extraFields = array_merge($extraFields, $data);
            }

            $connection->setMember($this->getMember());
            $connection->setType(Connection::TYPE_PUSH);

            $this->connectionManager->persist($connection);

            $data['entry'] = $connection->toArray();
            $data['entry_entity'] = 'connection';
        } else {
            $data = $this->getFormErrors($form);
            return $this->jsonResponse($data, JsonResponse::HTTP_BAD_REQUEST);
        }

        return $this->jsonResponse($data);
    }

    #[Route(path: '/push/delete', name: 'delete', methods: ['POST'], priority: 20)]
    public function delete(Request $request): JsonResponse
    {
        $data = [];

        $content = $this->getContent($request);

        if (true === isset($content['endpoint']) && '' !== $content['endpoint']) {
            if ($connection = $this->connectionManager->getOne(['type' => Connection::TYPE_PUSH, 'token' => $content['endpoint'], 'member' => $this->getMember()])) {
                $data['entry'] = $connection->toArray();
                $data['entry_entity'] = 'connection';

                $this->connectionManager->remove($connection);
            } else {
                return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
            }
        } else {
            $data['errors'][] = [
                'status' => '400',
                'source' => [
                    'pointer' => '/data/attributes/endpoint',
                ],
                'title' => 'Invalid attribute',
                'detail' => 'This value should not be blank.',
            ];

            return $this->jsonResponse($data, JsonResponse::HTTP_BAD_REQUEST);
        }

        return $this->jsonResponse($data);
    }
}
