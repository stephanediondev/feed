<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Entity\Collection;
use App\Entity\CollectionFeed;
use App\Entity\Feed;
use App\Manager\CollectionFeedManager;
use App\Manager\CollectionManager;
use App\Manager\FeedManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CollectionFeedManagerTest extends KernelTestCase
{
    protected CollectionManager $collectionManager;

    protected FeedManager $feedManager;

    protected CollectionFeedManager $collectionFeedManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->collectionManager = static::getContainer()->get('App\Manager\CollectionManager');

        $this->feedManager = static::getContainer()->get('App\Manager\FeedManager');

        $this->collectionFeedManager = static::getContainer()->get('App\Manager\CollectionFeedManager');
    }

    public function testPersist(): void
    {
        $collection = new Collection();

        $this->collectionManager->persist($collection);

        $feed = new Feed();
        $feed->setTitle('test-'.uniqid('', true));
        $feed->setLink('test-'.uniqid('', true));

        $this->feedManager->persist($feed);

        $collectionFeed = new CollectionFeed();
        $collectionFeed->setCollection($collection);
        $collectionFeed->setFeed($feed);

        $this->collectionFeedManager->persist($collectionFeed);

        $this->assertIsInt($collectionFeed->getId());

        $this->collectionFeedManager->remove($collectionFeed);

        $this->feedManager->remove($feed);

        $this->collectionManager->remove($collection);
    }

    public function testGetOne(): void
    {
        $test = $this->collectionFeedManager->getOne(['id' => 0]);
        $this->assertNull($test);
    }

    public function testGetList(): void
    {
        $test = $this->collectionFeedManager->getList(['id' => 0])->getResult();
        $this->assertIsArray($test);
        $this->assertCount(0, $test);
    }
}
