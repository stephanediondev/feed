<?php
namespace Readerself\CoreBundle\Event;

use Readerself\CoreBundle\Entity\Item;
use Symfony\Component\EventDispatcher\Event;

class ItemEvent extends Event
{
    protected $data;

    protected $mode;

    public function __construct(Item $data, $mode)
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
