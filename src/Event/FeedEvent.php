<?php

namespace App\Event;

use App\Entity\Feed;
use Symfony\Contracts\EventDispatcher\Event;

class FeedEvent extends Event
{
    protected $data;

    protected $mode;

    public function __construct(Feed $data, $mode)
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
