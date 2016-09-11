<?php
namespace Readerself\CoreBundle\Tests\Manager;

use Readerself\CoreBundle\Entity\Collection;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CollectionManagerTest extends KernelTestCase
{
    protected $collectionManager;

    protected function setUp()
    {
        self::bootKernel();

        $this->collectionManager = static::$kernel->getContainer()->get('readerself_core_manager_collection');
    }

    public function test()
    {
        $collection = $this->collectionManager->init();

        $collection_id = $this->collectionManager->persist($collection);

        $test = $this->collectionManager->getOne(['id' => $collection_id]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(Collection::class, $test);

        $this->collectionManager->remove($collection);

        $test = $this->collectionManager->getOne(['id' => $collection_id]);
        $this->assertNull($test);
    }
}
