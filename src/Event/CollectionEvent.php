<?php

namespace App\Event;

use App\Entity\Collection;
use Symfony\Contracts\EventDispatcher\Event;

class CollectionEvent extends Event
{
    protected $data;

    protected $mode;

    public function __construct(Collection $data, $mode)
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
