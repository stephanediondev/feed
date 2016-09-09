<?php
namespace Readerself\CoreBundle\Event;

use Readerself\CoreBundle\Entity\Category;
use Symfony\Component\EventDispatcher\Event;

class CategoryEvent extends Event
{
    protected $data;

    protected $mode;

    public function __construct(Category $data, $mode)
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
