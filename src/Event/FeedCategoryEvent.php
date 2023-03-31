<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\FeedCategory;
use Symfony\Contracts\EventDispatcher\Event;

class FeedCategoryEvent extends Event
{
    private FeedCategory $feedCategory;

    public const CREATED = 'feed_category.event.created';
    public const UPDATED = 'feed_category.event.updated';
    public const DELETED = 'feed_category.event.deleted';

    public function __construct(FeedCategory $feedCategory)
    {
        $this->feedCategory = $feedCategory;
    }

    public function getFeedCategory(): FeedCategory
    {
        return $this->feedCategory;
    }
}
