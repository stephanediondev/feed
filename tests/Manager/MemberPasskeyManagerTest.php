<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Entity\MemberPasskey;
use App\Entity\Member;
use App\Manager\MemberPasskeyManager;
use App\Manager\MemberManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MemberPasskeyManagerTest extends KernelTestCase
{
    protected MemberManager $memberManager;

    protected MemberPasskeyManager $memberPasskeyManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->memberManager = static::getContainer()->get('App\Manager\MemberManager');

        $this->memberPasskeyManager = static::getContainer()->get('App\Manager\MemberPasskeyManager');
    }

    public function testPersist(): void
    {
        $member = new Member();
        $member->setEmail(uniqid('phpunit-'));
        $member->setPassword(uniqid('phpunit-'));

        $this->memberManager->persist($member);

        $memberPasskey = new MemberPasskey();
        $memberPasskey->setTitle(uniqid('phpunit-'));
        $memberPasskey->setCredentialId(uniqid('phpunit-'));
        $memberPasskey->setPublicKey(uniqid('phpunit-'));
        $memberPasskey->setMember($member);

        $this->memberPasskeyManager->persist($memberPasskey);

        $this->assertIsInt($memberPasskey->getId());

        $this->memberPasskeyManager->remove($memberPasskey);

        $this->memberManager->remove($member);
    }

    public function testGetOne(): void
    {
        $test = $this->memberPasskeyManager->getOne(['id' => 0]);
        $this->assertNull($test);
    }

    public function testGetList(): void
    {
        $test = $this->memberPasskeyManager->getList(['id' => 0])->getResult();
        $this->assertIsArray($test);
        $this->assertCount(0, $test);
    }
}
