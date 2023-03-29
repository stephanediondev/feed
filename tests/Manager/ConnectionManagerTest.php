<?php
declare(strict_types=1);

namespace App\Tests\Manager;

use App\Entity\Connection;
use App\Manager\ConnectionManager;
use App\Manager\MemberManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ConnectionManagerTest extends KernelTestCase
{
    protected MemberManager $memberManager;

    protected ConnectionManager $connectionManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->memberManager = static::getContainer()->get('App\Manager\MemberManager');

        $this->connectionManager = static::getContainer()->get('App\Manager\ConnectionManager');
    }

    public function testId(): void
    {
        $token = 'test-'.uniqid('', true);
        $connection = $this->connectionManager->init();
        $connection->setMember($this->memberManager->getOne());
        $connection->setType('test');
        $connection->setToken($token);

        $connection_id = $this->connectionManager->persist($connection);

        $test = $this->connectionManager->getOne(['id' => $connection_id]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(Connection::class, $test);

        $this->connectionManager->remove($connection);

        $test = $this->connectionManager->getOne(['id' => $connection_id]);
        $this->assertNull($test);
    }

    public function testToken(): void
    {
        $token = 'test-'.uniqid('', true);
        $connection = $this->connectionManager->init();
        $connection->setMember($this->memberManager->getOne());
        $connection->setType('test');
        $connection->setToken($token);

        $connection_id = $this->connectionManager->persist($connection);

        $test = $this->connectionManager->getOne(['token' => $token]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(Connection::class, $test);
        $this->assertEquals($token, $test->getToken());

        $this->connectionManager->remove($connection);

        $test = $this->connectionManager->getOne(['token' => $token]);
        $this->assertNull($test);
    }
}
