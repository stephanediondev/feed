<?php
namespace Axipi\MCoreBundle\Tests\Manager;

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

    public function test()
    {
        $data = $this->categoryManager->init();
        $data->setTitle('test-unitaire-'.uniqid('', true));

        $id = $this->categoryManager->persist($data);

        $test = $this->categoryManager->getOne(['id' => $id]);

        $this->assertNotNull($test);
        $this->assertInstanceOf(Category::class, $test);

        $this->categoryManager->remove($data);

        $test = $this->categoryManager->getOne(['id' => $id]);

        $this->assertNull($test);
    }
}
