<?php

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

    public function test()
    {
        $member = $this->memberManager->init();
        $member->setEmail('test-'.uniqid('', true));
        $member->setPassword('test-'.uniqid('', true));

        $member_id = $this->memberManager->persist($member);

        $test = $this->memberManager->getOne(['id' => $member_id]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(Member::class, $test);

        $this->memberManager->remove($member);

        $test = $this->memberManager->getOne(['id' => $member_id]);
        $this->assertNull($test);
    }
}
