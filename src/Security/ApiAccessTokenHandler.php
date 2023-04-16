<?php

namespace App\Security;

use App\Entity\Connection;
use App\Helper\JwtHelper;
use App\Manager\ConnectionManager;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class ApiAccessTokenHandler implements AccessTokenHandlerInterface
{
    private ConnectionManager $connectionManager;

    public function __construct(ConnectionManager $connectionManager)
    {
        $this->connectionManager = $connectionManager;
    }

    public function getUserBadgeFrom(string $token): UserBadge
    {
        try {
            $payloadjwtPayloadModel = JwtHelper::getPayload($token);
        } catch (\Exception $e) {
            throw new BadCredentialsException('Invalid token.');
        }

        if (null === $payloadjwtPayloadModel) {
            throw new BadCredentialsException('Invalid token.');
        }

        $connection = $this->connectionManager->getOne(['type' => Connection::TYPE_LOGIN, 'token' => $payloadjwtPayloadModel->getJwtId()]);

        if (null === $connection) {
            throw new BadCredentialsException('Token not found.');
        }

        if (null === $connection->getMember() || null === $connection->getMember()->getEmail()) {
            throw new BadCredentialsException('Member not found.');
        }

        return new UserBadge($connection->getMember()->getEmail());
    }
}
