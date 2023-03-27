<?php

namespace App\Manager;

use App\Entity\CollectionFeed;
use App\Event\CollectionFeedEvent;
use App\Manager\AbstractManager;
use App\Repository\CollectionFeedRepository;

class CollectionFeedManager extends AbstractManager
{
    private CollectionFeedRepository $collectionFeedRepository;

    public function __construct(CollectionFeedRepository $collectionFeedRepository)
    {
        $this->collectionFeedRepository = $collectionFeedRepository;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?CollectionFeed
    {
        return $this->collectionFeedRepository->getOne($parameters);
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getList(array $parameters = []): mixed
    {
        return $this->collectionFeedRepository->getList($parameters);
    }

    public function init(): CollectionFeed
    {
        return new CollectionFeed();
    }

    public function persist(CollectionFeed $collectionFeed): ?int
    {
        if ($collectionFeed->getDateCreated() == null) {
            $eventName = CollectionFeedEvent::CREATED;
            $collectionFeed->setDateCreated(new \Datetime());
        } else {
            $eventName = CollectionFeedEvent::UPDATED;
        }

        $this->collectionFeedRepository->persist($collectionFeed);

        $event = new CollectionFeedEvent($collectionFeed);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();

        return $collectionFeed->getId();
    }

    public function remove(CollectionFeed $collectionFeed): void
    {
        $event = new CollectionFeedEvent($collectionFeed);
        $this->eventDispatcher->dispatch($event, CollectionFeedEvent::DELETED);

        $this->collectionFeedRepository->remove($collectionFeed);

        $this->clearCache();
    }
}
