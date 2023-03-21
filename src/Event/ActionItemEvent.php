<?php

namespace App\Event;

use App\Entity\ActionItem;
use Symfony\Contracts\EventDispatcher\Event;

class ActionItemEvent extends Event
{
    private ActionItem $actionItem;

    public const CREATED = 'action_item.event.created';
    public const UPDATED = 'action_item.event.updated';
    public const DELETED = 'action_item.event.deleted';

    public function __construct(ActionItem $actionItem)
    {
        $this->actionItem = $actionItem;
    }

    public function getActionItem(): ActionItem
    {
        return $this->actionItem;
    }
}
