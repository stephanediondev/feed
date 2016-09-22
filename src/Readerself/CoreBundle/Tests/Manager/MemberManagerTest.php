<?php
namespace Readerself\CoreBundle\Tests\Manager;

use Readerself\CoreBundle\Entity\Member;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MemberManagerTest extends KernelTestCase
{
    protected $memberManager;

    protected function setUp()
    {
        self::bootKernel();

        $this->memberManager = static::$kernel->getContainer()->get('readerself_core_manager_member');
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
