<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Entity\Connection;
use App\Helper\JwtHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route(path: '/api', name: 'api_logout_', priority: 20)]
class LogoutController extends AbstractAppController
{
    #[Route(path: '/logout', name: 'index', methods: ['GET'])]
    public function logout(Request $request): JsonResponse
    {
        $data = [];

        try {
            if ($request->headers->get('Authorization')) {
                $payloadjwtPayloadModel = JwtHelper::getPayload(str_replace('Bearer ', '', $request->headers->get('Authorization')));
                if ($payloadjwtPayloadModel) {
                    $token = $payloadjwtPayloadModel->getJwtId();

                    if ($connection = $this->connectionManager->getOne(['type' => Connection::TYPE_LOGIN, 'token' => $token, 'member' => $this->getMember()])) {
                        $data['entry'] = $connection->toArray();
                        $data['entry_entity'] = 'connection';

                        $this->connectionManager->remove($connection);
                    }
                }
            }
        } catch (\Exception $e) {
            throw new AccessDeniedException();
        }

        return $this->jsonResponse($data);
    }
}
