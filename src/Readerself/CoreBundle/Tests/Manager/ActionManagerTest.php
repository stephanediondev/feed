<?php
namespace Readerself\CoreBundle\Tests\Manager;

use Readerself\CoreBundle\Entity\Action;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ActionManagerTest extends KernelTestCase
{
    protected $actionManager;

    protected function setUp()
    {
        self::bootKernel();

        $this->actionManager = static::$kernel->getContainer()->get('readerself_core_manager_action');
    }

    public function testId()
    {
        $title = 'test-'.uniqid('', true);
        $action = $this->actionManager->init();
        $action->setTitle($title);

        $action_id = $this->actionManager->persist($action);

        $test = $this->actionManager->getOne(['id' => $action_id]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(Action::class, $test);

        $this->actionManager->remove($action);

        $test = $this->actionManager->getOne(['id' => $action_id]);
        $this->assertNull($test);
    }

    public function testTitle()
    {
        $title = 'test-'.uniqid('', true);
        $action = $this->actionManager->init();
        $action->setTitle($title);

        $action_id = $this->actionManager->persist($action);

        $test = $this->actionManager->getOne(['title' => $title]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(Action::class, $test);
        $this->assertEquals($title, $test->getTitle());

        $this->actionManager->remove($action);

        $test = $this->actionManager->getOne(['title' => $title]);
        $this->assertNull($test);
    }
}
