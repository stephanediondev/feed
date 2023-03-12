<?php

namespace App\Event;

use App\Entity\Item;
use Symfony\Contracts\EventDispatcher\Event;

class ItemEvent extends Event
{
    protected $data;

    protected $mode;

    public function __construct(Item $data, $mode)
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
