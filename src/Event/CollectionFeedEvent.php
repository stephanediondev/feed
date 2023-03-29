<?php
declare(strict_types=1);

namespace App\Event;

use App\Entity\CollectionFeed;
use Symfony\Contracts\EventDispatcher\Event;

class CollectionFeedEvent extends Event
{
    private CollectionFeed $collectionFeed;

    public const CREATED = 'collection_feed.event.created';
    public const UPDATED = 'collection_feed.event.updated';
    public const DELETED = 'collection_feed.event.deleted';

    public function __construct(CollectionFeed $collectionFeed)
    {
        $this->collectionFeed = $collectionFeed;
    }

    public function getCollectionFeed(): CollectionFeed
    {
        return $this->collectionFeed;
    }
}
