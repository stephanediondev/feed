<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\ItemCategory;
use App\Event\ItemCategoryEvent;
use App\Manager\AbstractManager;
use App\Repository\ItemCategoryRepository;

class ItemCategoryManager extends AbstractManager
{
    private ItemCategoryRepository $itemCategoryRepository;

    public function __construct(ItemCategoryRepository $itemCategoryRepository)
    {
        $this->itemCategoryRepository = $itemCategoryRepository;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?ItemCategory
    {
        return $this->itemCategoryRepository->getOne($parameters);
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getList(array $parameters = []): mixed
    {
        return $this->itemCategoryRepository->getList($parameters);
    }

    public function persist(ItemCategory $itemCategory): void
    {
        if ($itemCategory->getId() === null) {
            $eventName = ItemCategoryEvent::CREATED;
        } else {
            $eventName = ItemCategoryEvent::UPDATED;
        }

        $this->itemCategoryRepository->persist($itemCategory);

        $event = new ItemCategoryEvent($itemCategory);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();
    }

    public function remove(ItemCategory $itemCategory): void
    {
        $event = new ItemCategoryEvent($itemCategory);
        $this->eventDispatcher->dispatch($event, ItemCategoryEvent::DELETED);

        $this->itemCategoryRepository->remove($itemCategory);

        $this->clearCache();
    }
}
