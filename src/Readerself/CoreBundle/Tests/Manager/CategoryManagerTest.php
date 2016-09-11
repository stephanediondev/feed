<?php
namespace Readerself\CoreBundle\Tests\Manager;

use Readerself\CoreBundle\Entity\Category;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CategoryManagerTest extends KernelTestCase
{
    protected $categoryManager;

    protected function setUp()
    {
        self::bootKernel();

        $this->categoryManager = static::$kernel->getContainer()->get('readerself_core_manager_category');
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
