<?php

namespace App\Event;

use App\Entity\Enclosure;
use Symfony\Contracts\EventDispatcher\Event;

class EnclosureEvent extends Event
{
    private Enclosure $enclosure;

    public const CREATED = 'enclosure.event.created';
    public const UPDATED = 'enclosure.event.updated';
    public const DELETED = 'enclosure.event.deleted';

    public function __construct(Enclosure $enclosure)
    {
        $this->enclosure = $enclosure;
    }

    public function getEnclosure(): Enclosure
    {
        return $this->enclosure;
    }
}
