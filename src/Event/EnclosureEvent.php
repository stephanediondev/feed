<?php

namespace App\Event;

use App\Entity\Enclosure;
use Symfony\Contracts\EventDispatcher\Event;

class EnclosureEvent extends Event
{
    protected $data;

    protected $mode;

    public function __construct(Enclosure $data, $mode)
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
