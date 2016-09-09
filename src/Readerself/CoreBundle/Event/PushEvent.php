<?php
namespace Readerself\CoreBundle\Event;

use Readerself\CoreBundle\Entity\Push;
use Symfony\Component\EventDispatcher\Event;

class PushEvent extends Event
{
    protected $data;

    protected $mode;

    public function __construct(Push $data, $mode)
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
