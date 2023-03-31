<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\ActionFeed;
use Symfony\Contracts\EventDispatcher\Event;

class ActionFeedEvent extends Event
{
    private ActionFeed $actionFeed;

    public const CREATED = 'action_feed.event.created';
    public const UPDATED = 'action_feed.event.updated';
    public const DELETED = 'action_feed.event.deleted';

    public function __construct(ActionFeed $actionFeed)
    {
        $this->actionFeed = $actionFeed;
    }

    public function getActionFeed(): ActionFeed
    {
        return $this->actionFeed;
    }
}
