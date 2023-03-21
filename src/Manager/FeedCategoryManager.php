<?php

namespace App\Manager;

use App\Entity\FeedCategory;
use App\Event\FeedCategoryEvent;
use App\Manager\AbstractManager;
use App\Repository\FeedCategoryRepository;

class FeedCategoryManager extends AbstractManager
{
    private FeedCategoryRepository $feedCategoryRepository;

    public function __construct(FeedCategoryRepository $feedCategoryRepository)
    {
        $this->feedCategoryRepository = $feedCategoryRepository;
    }

    public function getOne($parameters = []): ?FeedCategory
    {
        return $this->feedCategoryRepository->getOne($parameters);
    }

    public function getList($parameters = [])
    {
        return $this->feedCategoryRepository->getList($parameters);
    }

    public function init()
    {
        return new FeedCategory();
    }

    public function persist($data)
    {
        if ($data->getDateCreated() == null) {
            $eventName = FeedCategoryEvent::CREATED;
            $data->setDateCreated(new \Datetime());
        } else {
            $eventName = FeedCategoryEvent::UPDATED;
        }
        $data->setDateModified(new \Datetime());

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        $event = new FeedCategoryEvent($data);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new FeedCategoryEvent($data);
        $this->eventDispatcher->dispatch($event, FeedCategoryEvent::DELETED);

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        $this->clearCache();
    }
}
