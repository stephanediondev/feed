<?php
namespace Readerself\CoreBundle\Event;

use Readerself\CoreBundle\Entity\ActionItemEvent;
use Symfony\Component\EventDispatcher\Event;

class ActionItemEvent extends Event
{
    protected $data;

    protected $mode;

    public function __construct(ActionItem $data, $mode)
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
