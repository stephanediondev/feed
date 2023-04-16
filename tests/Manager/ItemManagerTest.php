<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Entity\Feed;
use App\Entity\Item;
use App\Manager\FeedManager;
use App\Manager\ItemManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ItemManagerTest extends KernelTestCase
{
    protected FeedManager $feedManager;

    protected ItemManager $itemManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->feedManager = static::getContainer()->get('App\Manager\FeedManager');

        $this->itemManager = static::getContainer()->get('App\Manager\ItemManager');
    }

    public function testPersist(): void
    {
        $feed = new Feed();
        $feed->setTitle('test-'.uniqid('', true));
        $feed->setLink('test-'.uniqid('', true));

        $this->feedManager->persist($feed);

        $item = new Item();
        $item->setFeed($feed);
        $item->setTitle('test-'.uniqid('', true));
        $item->setLink('test-'.uniqid('', true));
        $item->setDate(new \Datetime());

        $this->itemManager->persist($item);

        $this->assertIsInt($item->getId());

        $this->itemManager->remove($item);

        $this->feedManager->remove($feed);
    }

    public function testGetOne(): void
    {
        $test = $this->itemManager->getOne(['id' => 0]);
        $this->assertNull($test);
    }

    public function testGetList(): void
    {
        $test = $this->itemManager->getList(['id' => 0, 'sortField' => 'itm.id', 'sortDirection' => 'ASC'])->getResult();
        $this->assertIsArray($test);
        $this->assertCount(0, $test);
    }
}
