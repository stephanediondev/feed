<?php

namespace App\Event;

use App\Entity\Category;
use Symfony\Contracts\EventDispatcher\Event;

class CategoryEvent extends Event
{
    protected $data;

    protected $mode;

    public function __construct(Category $data, $mode)
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
