<?php

namespace App\Event;

use App\Entity\ActionCategory;
use Symfony\Contracts\EventDispatcher\Event;

class ActionCategoryEvent extends Event
{
    private ActionCategory $actionCategory;

    public const CREATED = 'action_category.event.created';
    public const UPDATED = 'action_category.event.updated';
    public const DELETED = 'action_category.event.deleted';

    public function __construct(ActionCategory $actionCategory)
    {
        $this->actionCategory = $actionCategory;
    }

    public function getActionCategory(): ActionCategory
    {
        return $this->actionCategory;
    }
}
