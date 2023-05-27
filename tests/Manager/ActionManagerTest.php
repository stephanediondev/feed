<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Entity\Action;
use App\Manager\ActionManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ActionManagerTest extends KernelTestCase
{
    protected ActionManager $actionManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->actionManager = static::getContainer()->get('App\Manager\ActionManager');
    }

    public function testPersist(): void
    {
        $action = new Action();
        $action->setTitle(uniqid('phpunit-'));

        $this->actionManager->persist($action);

        $this->assertIsInt($action->getId());

        $this->actionManager->remove($action);
    }

    public function testGetOne(): void
    {
        $test = $this->actionManager->getOne(['id' => 0]);
        $this->assertNull($test);
    }

    public function testGetList(): void
    {
        $test = $this->actionManager->getList(['id' => 0])->getResult();
        $this->assertIsArray($test);
        $this->assertCount(0, $test);
    }
}
