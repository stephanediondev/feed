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

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?FeedCategory
    {
        return $this->feedCategoryRepository->getOne($parameters);
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getList(array $parameters = []): mixed
    {
        return $this->feedCategoryRepository->getList($parameters);
    }

    public function init(): FeedCategory
    {
        return new FeedCategory();
    }

    public function persist(FeedCategory $data): int
    {
        if ($data->getId() == null) {
            $eventName = FeedCategoryEvent::CREATED;
        } else {
            $eventName = FeedCategoryEvent::UPDATED;
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        $event = new FeedCategoryEvent($data);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();

        return $data->getId();
    }

    public function remove(FeedCategory $data): void
    {
        $event = new FeedCategoryEvent($data);
        $this->eventDispatcher->dispatch($event, FeedCategoryEvent::DELETED);

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        $this->clearCache();
    }
}
