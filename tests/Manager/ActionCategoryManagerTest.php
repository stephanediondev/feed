<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Entity\Action;
use App\Entity\ActionCategory;
use App\Entity\Category;
use App\Manager\ActionCategoryManager;
use App\Manager\ActionManager;
use App\Manager\CategoryManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ActionCategoryManagerTest extends KernelTestCase
{
    protected ActionManager $actionManager;

    protected CategoryManager $categoryManager;

    protected ActionCategoryManager $actionCategoryManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->actionManager = static::getContainer()->get('App\Manager\ActionManager');

        $this->categoryManager = static::getContainer()->get('App\Manager\CategoryManager');

        $this->actionCategoryManager = static::getContainer()->get('App\Manager\ActionCategoryManager');
    }

    public function testPersist(): void
    {
        $action = new Action();
        $action->setTitle(uniqid('phpunit-'));

        $this->actionManager->persist($action);

        $category = new Category();
        $category->setTitle(uniqid('phpunit-'));

        $this->categoryManager->persist($category);

        $actionCategory = new ActionCategory();
        $actionCategory->setAction($action);
        $actionCategory->setCategory($category);

        $this->actionCategoryManager->persist($actionCategory);

        $this->assertIsInt($actionCategory->getId());

        $this->actionCategoryManager->remove($actionCategory);

        $this->categoryManager->remove($category);

        $this->actionManager->remove($action);
    }

    public function testGetOne(): void
    {
        $test = $this->actionCategoryManager->getOne(['id' => 0]);
        $this->assertNull($test);
    }

    public function testGetList(): void
    {
        $test = $this->actionCategoryManager->getList(['id' => 0])->getResult();
        $this->assertIsArray($test);
        $this->assertCount(0, $test);
    }
}
