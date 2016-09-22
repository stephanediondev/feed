<?php
namespace Readerself\CoreBundle\Tests\Manager;

use Readerself\CoreBundle\Entity\Push;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PushManagerTest extends KernelTestCase
{
    protected $memberManager;

    protected function setUp()
    {
        self::bootKernel();

        $this->memberManager = static::$kernel->getContainer()->get('readerself_core_manager_member');
    }

    public function testId()
    {
        $endpoint = 'test-'.uniqid('', true);
        $push = $this->memberManager->pushManager->init();
        $push->setMember($this->memberManager->getOne());
        $push->setEndpoint($endpoint);

        $push_id = $this->memberManager->pushManager->persist($push);

        $test = $this->memberManager->pushManager->getOne(['id' => $push_id]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(Push::class, $test);

        $this->memberManager->pushManager->remove($push);

        $test = $this->memberManager->pushManager->getOne(['id' => $push_id]);
        $this->assertNull($test);
    }

    public function testEndpoint()
    {
        $endpoint = 'test-'.uniqid('', true);
        $push = $this->memberManager->pushManager->init();
        $push->setMember($this->memberManager->getOne());
        $push->setEndpoint($endpoint);

        $push_id = $this->memberManager->pushManager->persist($push);

        $test = $this->memberManager->pushManager->getOne(['endpoint' => $endpoint]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(Push::class, $test);
        $this->assertEquals($endpoint, $test->getEndpoint());

        $this->memberManager->pushManager->remove($push);

        $test = $this->memberManager->pushManager->getOne(['endpoint' => $endpoint]);
        $this->assertNull($test);
    }
}
