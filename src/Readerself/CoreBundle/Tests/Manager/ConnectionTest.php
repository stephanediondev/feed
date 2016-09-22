<?php
namespace Readerself\CoreBundle\Tests\Manager;

use Readerself\CoreBundle\Entity\Connection;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ConnectionManagerTest extends KernelTestCase
{
    protected $memberManager;

    protected function setUp()
    {
        self::bootKernel();

        $this->memberManager = static::$kernel->getContainer()->get('readerself_core_manager_member');
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
