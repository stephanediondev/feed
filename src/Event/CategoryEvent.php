<?php

namespace App\Event;

use App\Entity\Category;
use Symfony\Contracts\EventDispatcher\Event;

class CategoryEvent extends Event
{
    private Category $category;

    public const CREATED = 'category.event.created';
    public const UPDATED = 'category.event.updated';
    public const DELETED = 'category.event.deleted';

    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }
}
