<?php
namespace Readerself\CoreBundle\Event;

use Readerself\CoreBundle\Entity\Member;
use Symfony\Component\EventDispatcher\Event;

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
