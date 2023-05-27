<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Entity\Member;
use App\Manager\MemberManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MemberManagerTest extends KernelTestCase
{
    protected MemberManager $memberManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->memberManager = static::getContainer()->get('App\Manager\MemberManager');
    }

    public function testPersist(): void
    {
        $member = new Member();
        $member->setEmail(uniqid('phpunit-'));
        $member->setPassword(uniqid('phpunit-'));

        $this->memberManager->persist($member);

        $this->assertIsInt($member->getId());

        $this->memberManager->remove($member);
    }

    public function testGetOne(): void
    {
        $test = $this->memberManager->getOne(['id' => 0]);
        $this->assertNull($test);
    }

    public function testGetList(): void
    {
        $test = $this->memberManager->getList(['id' => 0])->getResult();
        $this->assertIsArray($test);
        $this->assertCount(0, $test);
    }
}
