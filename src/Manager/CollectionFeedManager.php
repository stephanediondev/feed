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
            $mode = 'insert';
            $data->setDateCreated(new \Datetime());
        } else {
            $mode = 'update';
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        $event = new CollectionFeedEvent($data, $mode);
        $this->eventDispatcher->dispatch($event, 'CollectionFeed.after_persist');

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new CollectionFeedEvent($data, 'delete');
        $this->eventDispatcher->dispatch($event, 'CollectionFeed.before_remove');

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        $this->clearCache();
    }
}
