<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Helper\JwtHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api', name: 'api_logout_', priority: 20)]
class LogoutController extends AbstractAppController
{
    #[Route(path: '/logout', name: 'index', methods: ['GET'])]
    public function logout(Request $request): JsonResponse
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        try {
            $payloadjwtPayloadModel = JwtHelper::getPayload(str_replace('Bearer ', '', $request->headers->get('Authorization')));
            if ($payloadjwtPayloadModel) {
                $token = $payloadjwtPayloadModel->getJwtId();

                if ($connection = $this->connectionManager->getOne(['type' => 'login', 'token' => $token])) {
                    $data['entry'] = $connection->toArray();
                    $data['entry_entity'] = 'connection';

                    $this->connectionManager->remove($connection);
                }
            }
        } catch (\Exception $e) {
            throw new AccessDeniedHttpException();
        }

        return new JsonResponse($data);
    }
}
