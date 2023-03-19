<?php

namespace App\Event;

use App\Entity\ItemCategory;
use Symfony\Contracts\EventDispatcher\Event;

class ItemCategoryEvent extends Event
{
    protected $data;

    protected $mode;

    public function __construct(ItemCategory $data, $mode)
    {
        $this->data = $data;
        $this->mode = $mode;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getMode()
    {
        return $this->mode;
    }
}
