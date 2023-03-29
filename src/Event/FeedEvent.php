<?php
declare(strict_types=1);

namespace App\Event;

use App\Entity\Feed;
use Symfony\Contracts\EventDispatcher\Event;

class FeedEvent extends Event
{
    private Feed $feed;

    public const CREATED = 'feed.event.created';
    public const UPDATED = 'feed.event.updated';
    public const DELETED = 'feed.event.deleted';

    public function __construct(Feed $feed)
    {
        $this->feed = $feed;
    }

    public function getFeed(): Feed
    {
        return $this->feed;
    }
}
