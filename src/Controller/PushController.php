<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Entity\Connection;
use App\Form\Type\PushType;
use App\Form\Type\PushUnsubscribeType;
use App\Helper\DeviceDetectorHelper;
use App\Helper\MaxmindHelper;
use App\Model\PushModel;
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

        $status = JsonResponse::HTTP_UNAUTHORIZED;

        $push = new PushModel();
        $form = $this->createForm(PushType::class, $push);

        $content = $this->getContent($request);
        $form->submit($content);

        if ($form->isValid()) {
            $extraFields = DeviceDetectorHelper::asArray($request);

            if ($extraFields['ip'] && '127.0.0.1' != $extraFields['ip'] && true === $this->maxmindEnabled) {
                $data = MaxmindHelper::get($extraFields['ip']);
                $extraFields = array_merge($extraFields, $data);
            }

            $extraFields['public_key'] = $push->getPublicKey();
            $extraFields['authentication_secret'] = $push->getAuthenticationSecret();
            $extraFields['content_encoding'] = $push->getContentEncoding();

            $connection = new Connection();
            $connection->setMember($this->getUser());
            $connection->setType('push');
            $connection->setToken($push->getEndpoint());
            $connection->setExtraFields($extraFields);

            $this->connectionManager->persist($connection);

            $data['entry'] = $connection->toArray();
            $data['entry_entity'] = 'connection';

            $status = JsonResponse::HTTP_OK;
        }

        return new JsonResponse($data, $status);
    }

    #[Route(path: '/push/delete', name: 'delete', methods: ['POST'], priority: 20)]
    public function delete(Request $request): JsonResponse
    {
        $data = [];

        $status = JsonResponse::HTTP_UNAUTHORIZED;

        $push = new PushModel();
        $form = $this->createForm(PushUnsubscribeType::class, $push);

        $content = $this->getContent($request);
        $form->submit($content);

        if ($form->isValid()) {
            if ($connection = $this->connectionManager->getOne(['type' => 'push', 'token' => $push->getEndpoint()])) {
                $this->connectionManager->remove($connection);
            }

            $status = JsonResponse::HTTP_OK;
        }

        return new JsonResponse($data, $status);
    }
}
