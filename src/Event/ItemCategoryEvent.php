<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\ItemCategory;
use Symfony\Contracts\EventDispatcher\Event;

class ItemCategoryEvent extends Event
{
    private ItemCategory $itemCategory;

    public const CREATED = 'item_category.event.created';
    public const UPDATED = 'item_category.event.updated';
    public const DELETED = 'item_category.event.deleted';

    public function __construct(ItemCategory $itemCategory)
    {
        $this->itemCategory = $itemCategory;
    }

    public function getItemCategory(): ItemCategory
    {
        return $this->itemCategory;
    }
}
