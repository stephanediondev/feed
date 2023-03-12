<?php

namespace App\Event;

use App\Entity\Member;
use Symfony\Contracts\EventDispatcher\Event;

class MemberEvent extends Event
{
    protected $data;

    protected $mode;

    public function __construct(Member $data, $mode)
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
