<?php
namespace Readerself\CoreBundle\Event;

use Readerself\CoreBundle\Entity\Share;
use Symfony\Component\EventDispatcher\Event;

class ShareEvent extends Event
{
    protected $data;

    protected $mode;

    public function __construct(Share $data, $mode)
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
