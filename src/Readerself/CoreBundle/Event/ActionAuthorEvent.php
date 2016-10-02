<?php
namespace Readerself\CoreBundle\Event;

use Readerself\CoreBundle\Entity\ActionAuthor;
use Symfony\Component\EventDispatcher\Event;

class ActionAuthorEvent extends Event
{
    protected $data;

    protected $mode;

    public function __construct(ActionAuthor $data, $mode)
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
