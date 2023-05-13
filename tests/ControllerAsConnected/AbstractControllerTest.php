<?php

namespace App\Tests\ControllerAsConnected;

use App\Manager\ConnectionManager;
use App\Helper\JwtHelper;
use App\Model\JwtPayloadModel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Art4\JsonApiClient\Helper\Parser;
use Art4\JsonApiClient\Exception\InputException;
use Art4\JsonApiClient\Exception\ValidationException;

abstract class AbstractControllerTest extends WebTestCase
{
    protected $client;

    protected function setUp(): void
    {
        $container = static::getContainer();

        $connectionManager = $container->get(ConnectionManager::class);
        $testConnection = $connectionManager->getOne([]);

        $headers = [
            'CONTENT_TYPE' => 'application/json',
        ];

        if ($testConnection) {
            $jwtPayloadModel = new JwtPayloadModel();
            $jwtPayloadModel->setJwtId($testConnection->getToken());
            $jwtPayloadModel->setAudience(strval($testConnection->getMember()->getId()));

            $headers['HTTP_AUTHORIZATION'] = 'Bearer '.JwtHelper::createToken($jwtPayloadModel);
        }

        self::ensureKernelShutdown();
        $this->client = static::createClient([], $headers);
    }

    protected function isValidResponseString(string $json): bool
    {
        $isValidResponseString = Parser::isValidResponseString($json);

        if (false === $isValidResponseString) {
            try {
                // Use this if you have a response after calling a JSON API server
                Parser::parseResponseString($json);
            } catch (InputException $e) {
                // $jsonapiString is not valid JSON
                dump($e->getMessage());
            } catch (ValidationException $e) {
                // $jsonapiString is not valid JSON API
                dump($e->getMessage());
            }
        }

        return $isValidResponseString;
    }

    protected function retrieveOneId(string $path): ?int
    {
        $this->client->request('GET', $path);
        $json = $this->client->getResponse()->getContent();
        $content = json_decode($json, true);

        if (true === isset($content['data']) && 0 < count($content['data'])) {
            return intval($content['data'][0]['id']);
        }

        return null;
    }
}
