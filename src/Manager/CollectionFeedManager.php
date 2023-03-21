<?php

namespace App\Manager;

use App\Entity\CollectionFeed;
use App\Event\CollectionFeedEvent;
use App\Manager\AbstractManager;
use App\Repository\CollectionFeedRepository;

class CollectionFeedManager extends AbstractManager
{
    public CollectionFeedRepository $collectionFeedRepository;

    public function __construct(CollectionFeedRepository $collectionFeedRepository)
    {
        $this->collectionFeedRepository = $collectionFeedRepository;
    }

    public function getOne($parameters = []): ?CollectionFeed
    {
        return $this->collectionFeedRepository->getOne($parameters);
    }

    public function getList($parameters = [])
    {
        return $this->collectionFeedRepository->getList($parameters);
    }

    public function init()
    {
        return new CollectionFeed();
    }

    public function persist($data)
    {
        if ($data->getDateCreated() == null) {
            $eventName = CollectionFeedEvent::CREATED;
            $data->setDateCreated(new \Datetime());
        } else {
            $eventName = CollectionFeedEvent::UPDATED;
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        $event = new CollectionFeedEvent($data);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new CollectionFeedEvent($data);
        $this->eventDispatcher->dispatch($event, CollectionFeedEvent::DELETED);

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        $this->clearCache();
    }
}
