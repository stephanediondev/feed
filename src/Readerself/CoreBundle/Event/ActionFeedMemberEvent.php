<?php
namespace Readerself\CoreBundle\Event;

use Readerself\CoreBundle\Entity\ActionFeedMember;
use Symfony\Component\EventDispatcher\Event;

class ActionFeedMemberEvent extends Event
{
    protected $data;

    protected $mode;

    public function __construct(ActionFeedMember $data, $mode)
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
