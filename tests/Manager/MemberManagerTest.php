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
        $member->setEmail('test-'.uniqid('', true));
        $member->setPassword('test-'.uniqid('', true));

        $this->memberManager->persist($member);

        $test = $this->memberManager->getOne(['id' => $member->getId()]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(Member::class, $test);

        $this->memberManager->remove($member);
    }
}
