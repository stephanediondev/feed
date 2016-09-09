<?php
namespace Readerself\CoreBundle\Event;

use Readerself\CoreBundle\Entity\Folder;
use Symfony\Component\EventDispatcher\Event;

class FolderEvent extends Event
{
    protected $data;

    protected $mode;

    public function __construct(Folder $data, $mode)
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
