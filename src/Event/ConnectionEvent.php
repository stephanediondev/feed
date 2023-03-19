<?php

namespace App\Event;

use App\Entity\Connection;
use Symfony\Contracts\EventDispatcher\Event;

class ConnectionEvent extends Event
{
    protected $data;

    protected $mode;

    public function __construct(Connection $data, $mode)
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
