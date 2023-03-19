<?php

namespace App\Tests\Manager;

use App\Entity\Enclosure;
use App\Entity\Item;
use App\Entity\Feed;
use App\Manager\FeedManager;
use App\Manager\ItemManager;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EnclosureManagerTest extends KernelTestCase
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

        $enclosure = $this->itemManager->enclosureManager->init();
        $enclosure->setItem($item);
        $enclosure->setLink('test-'.uniqid('', true));
        $enclosure->setType('test-'.uniqid('', true));

        $enclosure_id = $this->itemManager->enclosureManager->persist($enclosure);

        $test = $this->itemManager->enclosureManager->getOne(['id' => $enclosure_id]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(Enclosure::class, $test);

        $this->itemManager->enclosureManager->remove($enclosure);

        $test = $this->itemManager->enclosureManager->getOne(['id' => $enclosure_id]);
        $this->assertNull($test);

        $this->itemManager->remove($item);

        $this->feedManager->remove($feed);
    }
}
