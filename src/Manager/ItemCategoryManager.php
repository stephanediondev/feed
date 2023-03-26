<?php

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

    public function init(): ItemCategory
    {
        return new ItemCategory();
    }

    public function persist(ItemCategory $data): ?int
    {
        if ($data->getId() == null) {
            $eventName = ItemCategoryEvent::CREATED;
        } else {
            $eventName = ItemCategoryEvent::UPDATED;
        }

        $this->itemCategoryRepository->persist($data);

        $event = new ItemCategoryEvent($data);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();

        return $data->getId();
    }

    public function remove(ItemCategory $data): void
    {
        $event = new ItemCategoryEvent($data);
        $this->eventDispatcher->dispatch($event, ItemCategoryEvent::DELETED);

        $this->itemCategoryRepository->remove($data);

        $this->clearCache();
    }
}
