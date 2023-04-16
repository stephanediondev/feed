<?php

declare(strict_types=1);

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

    public function testPersist(): void
    {
        $collection = new Collection();

        $this->collectionManager->persist($collection);

        $this->assertIsInt($collection->getId());

        $this->collectionManager->remove($collection);
    }

    public function testGetOne(): void
    {
        $test = $this->collectionManager->getOne(['id' => 0]);
        $this->assertNull($test);
    }

    public function testGetList(): void
    {
        $test = $this->collectionManager->getList(['id' => 0])->getResult();
        $this->assertIsArray($test);
        $this->assertCount(0, $test);
    }
}
