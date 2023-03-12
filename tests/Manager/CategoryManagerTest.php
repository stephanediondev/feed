<?php

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

    public function testId()
    {
        $title = 'test-'.uniqid('', true);
        $category = $this->categoryManager->init();
        $category->setTitle($title);

        $category_id = $this->categoryManager->persist($category);

        $test = $this->categoryManager->getOne(['id' => $category_id]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(Category::class, $test);

        $this->categoryManager->remove($category);

        $test = $this->categoryManager->getOne(['id' => $category_id]);
        $this->assertNull($test);
    }

    public function testTitle()
    {
        $title = 'test-'.uniqid('', true);
        $category = $this->categoryManager->init();
        $category->setTitle($title);

        $category_id = $this->categoryManager->persist($category);

        $test = $this->categoryManager->getOne(['title' => $title]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(Category::class, $test);
        $this->assertEquals($title, $test->getTitle());

        $this->categoryManager->remove($category);

        $test = $this->categoryManager->getOne(['title' => $title]);
        $this->assertNull($test);
    }
}
