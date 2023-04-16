<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Entity\Enclosure;
use App\Entity\Feed;
use App\Entity\Item;
use App\Manager\EnclosureManager;
use App\Manager\FeedManager;
use App\Manager\ItemManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EnclosureManagerTest extends KernelTestCase
{
    protected FeedManager $feedManager;

    protected ItemManager $itemManager;

    protected EnclosureManager $enclosureManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->feedManager = static::getContainer()->get('App\Manager\FeedManager');

        $this->itemManager = static::getContainer()->get('App\Manager\ItemManager');

        $this->enclosureManager = static::getContainer()->get('App\Manager\EnclosureManager');
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

        $enclosure = new Enclosure();
        $enclosure->setItem($item);
        $enclosure->setLink('test-'.uniqid('', true));
        $enclosure->setType('test-'.uniqid('', true));

        $this->enclosureManager->persist($enclosure);

        $this->assertIsInt($enclosure->getId());

        $this->enclosureManager->remove($enclosure);

        $this->itemManager->remove($item);

        $this->feedManager->remove($feed);
    }

    public function testGetOne(): void
    {
        $test = $this->enclosureManager->getOne(['id' => 0]);
        $this->assertNull($test);
    }

    public function testGetList(): void
    {
        $test = $this->enclosureManager->getList(['id' => 0])->getResult();
        $this->assertIsArray($test);
        $this->assertCount(0, $test);
    }
}
