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

    public function persist(CollectionFeed $data): ?int
    {
        if ($data->getDateCreated() == null) {
            $eventName = CollectionFeedEvent::CREATED;
            $data->setDateCreated(new \Datetime());
        } else {
            $eventName = CollectionFeedEvent::UPDATED;
        }

        $this->collectionFeedRepository->persist($data);

        $event = new CollectionFeedEvent($data);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();

        return $data->getId();
    }

    public function remove(CollectionFeed $data): void
    {
        $event = new CollectionFeedEvent($data);
        $this->eventDispatcher->dispatch($event, CollectionFeedEvent::DELETED);

        $this->collectionFeedRepository->remove($data);

        $this->clearCache();
    }
}
