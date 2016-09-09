<?php
namespace Readerself\CoreBundle\Event;

use Readerself\CoreBundle\Entity\ActionItemMember;
use Symfony\Component\EventDispatcher\Event;

class ActionItemMemberEvent extends Event
{
    protected $data;

    protected $mode;

    public function __construct(ActionItemMember $data, $mode)
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
