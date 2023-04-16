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
        $category->setTitle('test-'.uniqid('', true));

        $this->categoryManager->persist($category);

        $this->assertIsInt($category->getId());

        $this->categoryManager->remove($category);
    }

    public function testGetOne(): void
    {
        $test = $this->categoryManager->getOne(['id' => 0]);
        $this->assertNull($test);
    }
}
