<?php

namespace App\Tests\Manager;

use App\Entity\Item;
use App\Entity\Feed;
use App\Manager\ItemManager;
use App\Manager\FeedManager;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ItemManagerTest extends KernelTestCase
{
    protected ItemManager $itemManager;

    protected FeedManager $feedManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->itemManager = static::getContainer()->get('App\Manager\ItemManager');

        $this->feedManager = static::getContainer()->get('App\Manager\FeedManager');
    }

    public function test()
    {
        $feed = $this->feedManager->init();
        $feed->setTitle('test-'.uniqid('', true));
        $feed->setLink('test-'.uniqid('', true));

        $feed_id = $this->feedManager->persist($feed);

        $item = $this->itemManager->init();
        $item->setFeed($feed);
        $item->setTitle('test-'.uniqid('', true));
        $item->setLink('test-'.uniqid('', true));
        $item->setDate(new \Datetime());

        $item_id = $this->itemManager->persist($item);

        $test = $this->itemManager->getOne(['id' => $item_id]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(Item::class, $test);

        $this->itemManager->remove($item);

        $test = $this->itemManager->getOne(['id' => $item_id]);
        $this->assertNull($test);

        $this->feedManager->remove($feed);
    }
}
