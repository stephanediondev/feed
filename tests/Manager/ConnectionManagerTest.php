<?php

namespace App\Tests\Manager;

use App\Entity\Connection;
use App\Manager\MemberManager;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ConnectionManagerTest extends KernelTestCase
{
    protected MemberManager $memberManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->memberManager = static::getContainer()->get('App\Manager\MemberManager');
    }

    public function testId()
    {
        $token = 'test-'.uniqid('', true);
        $connection = $this->memberManager->connectionManager->init();
        $connection->setMember($this->memberManager->getOne());
        $connection->setType('test');
        $connection->setToken($token);

        $connection_id = $this->memberManager->connectionManager->persist($connection);

        $test = $this->memberManager->connectionManager->getOne(['id' => $connection_id]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(Connection::class, $test);

        $this->memberManager->connectionManager->remove($connection);

        $test = $this->memberManager->connectionManager->getOne(['id' => $connection_id]);
        $this->assertNull($test);
    }

    public function testToken()
    {
        $token = 'test-'.uniqid('', true);
        $connection = $this->memberManager->connectionManager->init();
        $connection->setMember($this->memberManager->getOne());
        $connection->setType('test');
        $connection->setToken($token);

        $connection_id = $this->memberManager->connectionManager->persist($connection);

        $test = $this->memberManager->connectionManager->getOne(['token' => $token]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(Connection::class, $test);
        $this->assertEquals($token, $test->getToken());

        $this->memberManager->connectionManager->remove($connection);

        $test = $this->memberManager->connectionManager->getOne(['token' => $token]);
        $this->assertNull($test);
    }
}
