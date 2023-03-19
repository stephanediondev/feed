<?php

namespace App\Event;

use App\Entity\FeedCategory;
use Symfony\Contracts\EventDispatcher\Event;

class FeedCategoryEvent extends Event
{
    protected $data;

    protected $mode;

    public function __construct(FeedCategory $data, $mode)
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
