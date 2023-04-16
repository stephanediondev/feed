<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Entity\Connection;
use App\Helper\DeviceDetectorHelper;
use App\Helper\MaxmindHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api', name: 'api_pinboard_', priority: 20)]
class PinboardController extends AbstractAppController
{
    private bool $maxmindEnabled;

    public function __construct(bool $maxmindEnabled)
    {
        $this->maxmindEnabled = $maxmindEnabled;
    }

    #[Route('/pinboard', name: 'index', methods: ['POST'])]
    public function pinboard(Request $request): JsonResponse
    {
        $data = [];

        $content = $this->getContent($request);

        if (true === isset($content['token']) && '' !== $content['token']) {
            $connection = $this->connectionManager->getOne(['type' => Connection::TYPE_PINBOARD, 'member' => $this->getMember()]);

            if ($connection) {
                $connection->setToken($content['token']);
            } else {
                $extraFields = DeviceDetectorHelper::asArray($request);

                if ($extraFields['ip'] && '127.0.0.1' != $extraFields['ip'] && true === $this->maxmindEnabled) {
                    $data = MaxmindHelper::get($extraFields['ip']);
                    $extraFields = array_merge($extraFields, $data);
                }

                $connection = new Connection();
                $connection->setMember($this->getMember());
                $connection->setType(Connection::TYPE_PINBOARD);
                $connection->setToken($content['token']);
                $connection->setExtraFields($extraFields);
            }
            $this->connectionManager->persist($connection);

            $data['entry'] = $connection->toArray();
            $data['entry_entity'] = 'connection';
        } else {
            $data['errors'][] = [
                'status' => '400',
                'source' => [
                    'pointer' => '/data/attributes/token',
                ],
                'title' => 'Invalid attribute',
                'detail' => 'This value should not be blank.',
            ];

            return $this->jsonResponse($data, JsonResponse::HTTP_BAD_REQUEST);
        }

        return $this->jsonResponse($data);
    }
}
