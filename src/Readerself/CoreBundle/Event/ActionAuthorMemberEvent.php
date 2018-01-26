<?php
namespace Readerself\CoreBundle\Event;

use Readerself\CoreBundle\Entity\ActionAuthorMember;
use Symfony\Component\EventDispatcher\Event;

class ActionAuthorMemberEvent extends Event
{
    protected $data;

    protected $mode;

    public function __construct(ActionAuthorMember $data, $mode)
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
