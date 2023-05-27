<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Entity\Category;
use App\Manager\CategoryManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CategoryManagerTest extends KernelTestCase
{
    protected CategoryManager $categoryManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->categoryManager = static::getContainer()->get('App\Manager\CategoryManager');
    }

    public function testPersist(): void
    {
        $category = new Category();
        $category->setTitle(uniqid('phpunit-'));

        $this->categoryManager->persist($category);

        $this->assertIsInt($category->getId());

        $this->categoryManager->remove($category);
    }

    public function testGetOne(): void
    {
        $test = $this->categoryManager->getOne(['id' => 0]);
        $this->assertNull($test);
    }

    public function testGetList(): void
    {
        $test = $this->categoryManager->getList(['id' => 0, 'sortField' => 'cat.id', 'sortDirection' => 'ASC'])->getResult();
        $this->assertIsArray($test);
        $this->assertCount(0, $test);
    }
}
