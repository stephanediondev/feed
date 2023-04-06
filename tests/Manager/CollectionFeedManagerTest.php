<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Entity\Collection;
use App\Entity\CollectionFeed;
use App\Entity\Feed;
use App\Manager\CollectionManager;
use App\Manager\CollectionFeedManager;
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

        $test = $this->collectionFeedManager->getOne(['id' => $collectionFeed->getId()]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(CollectionFeed::class, $test);

        $this->collectionFeedManager->remove($collectionFeed);

        $this->feedManager->remove($feed);

        $this->collectionManager->remove($collection);
    }
}
