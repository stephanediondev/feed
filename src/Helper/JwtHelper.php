<?php
declare(strict_types=1);

namespace App\Helper;

use App\Model\JwtPayloadModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use stdClass;

class JwtHelper
{
    public const ALGORITHM = 'RS512';

    public static function createToken(JwtPayloadModel $jwtPayloadModel, string $privateKey = 'application.key'): ?string
    {
        if ($key = self::getPrivateKey($privateKey)) {
            return JWT::encode($jwtPayloadModel->toArray(), $key, self::ALGORITHM);
        }

        return null;
    }

    public static function getPayload(string $token, string $publicKey = 'application.pub'): ?JwtPayloadModel
    {
        if ($key = self::getPublicKey($publicKey)) {
            $payload = JWT::decode($token, new Key($key, self::ALGORITHM));
            return self::fromStdClass($payload);
        }

        return null;
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
