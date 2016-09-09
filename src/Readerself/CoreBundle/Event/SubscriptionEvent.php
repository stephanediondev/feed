<?php
namespace Readerself\CoreBundle\Event;

use Readerself\CoreBundle\Entity\Subscription;
use Symfony\Component\EventDispatcher\Event;

class SubscriptionEvent extends Event
{
    protected $data;

    protected $mode;

    public function __construct(Subscription $data, $mode)
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
