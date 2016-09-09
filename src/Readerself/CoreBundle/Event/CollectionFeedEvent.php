<?php
namespace Readerself\CoreBundle\Event;

use Readerself\CoreBundle\Entity\CollectionFeed;
use Symfony\Component\EventDispatcher\Event;

class CollectionFeedEvent extends Event
{
    protected $data;

    protected $mode;

    public function __construct(CollectionFeed $data, $mode)
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
