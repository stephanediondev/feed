<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Collection;
use Symfony\Contracts\EventDispatcher\Event;

class CollectionEvent extends Event
{
    private Collection $collection;

    public const CREATED = 'collection.event.created';
    public const UPDATED = 'collection.event.updated';
    public const DELETED = 'collection.event.deleted';

    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    public function getCollection(): Collection
    {
        return $this->collection;
    }
}
