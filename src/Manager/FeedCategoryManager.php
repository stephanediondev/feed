<?php

declare(strict_types=1);

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

    public function persist(FeedCategory $feedCategory): void
    {
        if ($feedCategory->getId() == null) {
            $eventName = FeedCategoryEvent::CREATED;
        } else {
            $eventName = FeedCategoryEvent::UPDATED;
        }

        $this->feedCategoryRepository->persist($feedCategory);

        $event = new FeedCategoryEvent($feedCategory);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();
    }

    public function remove(FeedCategory $feedCategory): void
    {
        $event = new FeedCategoryEvent($feedCategory);
        $this->eventDispatcher->dispatch($event, FeedCategoryEvent::DELETED);

        $this->feedCategoryRepository->remove($feedCategory);

        $this->clearCache();
    }
}
