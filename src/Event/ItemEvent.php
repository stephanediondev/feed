<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Item;
use Symfony\Contracts\EventDispatcher\Event;

class ItemEvent extends Event
{
    private Item $item;

    public const CREATED = 'item.event.created';
    public const UPDATED = 'item.event.updated';
    public const DELETED = 'item.event.deleted';

    public function __construct(Item $item)
    {
        $this->item = $item;
    }

    public function getItem(): Item
    {
        return $this->item;
    }
}
