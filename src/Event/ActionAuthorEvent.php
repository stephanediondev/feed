<?php

namespace App\Event;

use App\Entity\ActionAuthor;
use Symfony\Contracts\EventDispatcher\Event;

class ActionAuthorEvent extends Event
{
    protected $data;

    protected $mode;

    public function __construct(ActionAuthor $data, $mode)
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
