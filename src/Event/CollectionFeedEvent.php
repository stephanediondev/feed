<?php

namespace App\Event;

use App\Entity\CollectionFeed;
use Symfony\Contracts\EventDispatcher\Event;

class CollectionFeedEvent extends Event
{
    protected $data;

    protected $mode;

    public function __construct(CollectionFeed $data, $mode)
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
