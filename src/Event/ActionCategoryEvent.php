<?php

namespace App\Event;

use App\Entity\ActionCategory;
use Symfony\Contracts\EventDispatcher\Event;

class ActionCategoryEvent extends Event
{
    protected $data;

    protected $mode;

    public function __construct(ActionCategory $data, $mode)
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
