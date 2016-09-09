<?php
namespace Readerself\CoreBundle\Event;

use Readerself\CoreBundle\Entity\Author;
use Symfony\Component\EventDispatcher\Event;

class AuthorEvent extends Event
{
    protected $data;

    protected $mode;

    public function __construct(Author $data, $mode)
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
