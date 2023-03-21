<?php

namespace App\Tests\Manager;

use App\Entity\Collection;
use App\Manager\CollectionManager;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CollectionManagerTest extends KernelTestCase
{
    protected CollectionManager $collectionManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->collectionManager = static::getContainer()->get('App\Manager\CollectionManager');
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