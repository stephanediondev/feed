<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Entity\Connection;
use App\Entity\Member;
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

    public function testPersist(): void
    {
        $member = new Member();
        $member->setEmail('test-'.uniqid('', true));
        $member->setPassword('test-'.uniqid('', true));

        $this->memberManager->persist($member);

        $connection = new Connection();
        $connection->setMember($member);
        $connection->setType('test-'.uniqid('', true));
        $connection->setToken('test-'.uniqid('', true));

        $this->connectionManager->persist($connection);

        $this->assertIsInt($connection->getId());

        $this->connectionManager->remove($connection);

        $this->memberManager->remove($member);
    }

    public function testGetOne(): void
    {
        $test = $this->connectionManager->getOne(['id' => 0]);
        $this->assertNull($test);
    }
}
