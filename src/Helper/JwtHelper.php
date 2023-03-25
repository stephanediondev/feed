<?php

namespace App\Helper;

use App\Model\JwtPayloadModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use stdClass;

class JwtHelper
{
    public const ALGORITHM = 'RS512';

    public static function createToken(JwtPayloadModel $jwtPayloadModel, string $privateKey = 'application.key'): string
    {
        $token = JWT::encode($jwtPayloadModel->toArray(), self::getPrivateKey($privateKey), self::ALGORITHM);

        return $token;
    }

    public static function getPayload(string $token, string $publicKey = 'application.pub'): JwtPayloadModel
    {
        $payload = JWT::decode($token, new Key(self::getPublicKey($publicKey), self::ALGORITHM));
        $jwtPayloadModel = self::fromStdClass($payload);

        return $jwtPayloadModel;
    }

    /**
     * @param int<1, max> $length
     */
    public static function generateUniqueIdentifier(int $length = 40): string
    {
        return \bin2hex(\random_bytes($length));
    }

    private static function getPrivateKey(string $privateKey): ?string
    {
        $file = __DIR__.'/../../config/jwt-keys/'.$privateKey;
        if (file_exists($file)) {
            if ($content = file_get_contents($file)) {
                return $content;
            }
        }

        return null;
    }

    private static function getPublicKey(string $publicKey): ?string
    {
        $file = __DIR__.'/../../config/jwt-keys/'.$publicKey;
        if (file_exists($file)) {
            if ($content = file_get_contents($file)) {
                return $content;
            }
        }

        return null;
    }

    private static function fromStdClass(stdClass $payload): JwtPayloadModel
    {
        $jwtPayloadModel = new JwtPayloadModel();

        if (true === isset($payload->jit)) {
            $jwtPayloadModel->setJwtId($payload->jit);
        }

        if (true === isset($payload->iss)) {
            $jwtPayloadModel->setIssuer($payload->iss);
        }

        if (true === isset($payload->aud)) {
            $jwtPayloadModel->setAudience($payload->aud);
        }

        if (true === isset($payload->sub)) {
            $jwtPayloadModel->setSubject($payload->sub);
        }

        if (true === isset($payload->exp)) {
            $jwtPayloadModel->setExpirationTime($payload->exp);
        }

        return $jwtPayloadModel;
    }
}
