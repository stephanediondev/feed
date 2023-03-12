<?php

namespace App\Event;

use App\Entity\ActionItem;
use Symfony\Contracts\EventDispatcher\Event;

class ActionItemEvent extends Event
{
    protected $data;

    protected $mode;

    public function __construct(ActionItem $data, $mode)
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
