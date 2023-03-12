<?php

namespace App\Event;

use App\Entity\Action;
use Symfony\Contracts\EventDispatcher\Event;

class ActionEvent extends Event
{
    protected $data;

    protected $mode;

    public function __construct(Action $data, $mode)
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
