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
        $action->setTitle('test-'.uniqid('', true));

        $this->actionManager->persist($action);

        $test = $this->actionManager->getOne(['id' => $action->getId()]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(Action::class, $test);

        $this->actionManager->remove($action);
    }
}
