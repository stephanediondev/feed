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

    public function testId(): void
    {
        $title = 'test-'.uniqid('', true);
        $category = new Category();
        $category->setTitle($title);

        $category_id = $this->categoryManager->persist($category);

        $test = $this->categoryManager->getOne(['id' => $category_id]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(Category::class, $test);

        $this->categoryManager->remove($category);

        $test = $this->categoryManager->getOne(['id' => $category_id]);
        $this->assertNull($test);
    }

    public function testTitle(): void
    {
        $title = 'test-'.uniqid('', true);
        $category = new Category();
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
