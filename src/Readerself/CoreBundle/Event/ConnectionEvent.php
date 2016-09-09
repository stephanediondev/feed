<?php
namespace Readerself\CoreBundle\Event;

use Readerself\CoreBundle\Entity\Connection;
use Symfony\Component\EventDispatcher\Event;

class ConnectionEvent extends Event
{
    protected $data;

    protected $mode;

    public function __construct(Connection $data, $mode)
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
