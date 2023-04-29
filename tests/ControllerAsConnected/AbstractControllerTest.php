<?php

namespace App\Tests\ControllerAsConnected;

use App\Manager\MemberManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractControllerTest extends WebTestCase
{
    protected $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $container = static::getContainer();

        $memberManager = $container->get(MemberManager::class);
        $testMember = $memberManager->getOne([]);

        $this->client->loginUser($testMember);
    }
}
