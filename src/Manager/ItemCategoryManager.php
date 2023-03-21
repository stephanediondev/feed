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

    public function getOne($parameters = []): ?ItemCategory
    {
        return $this->itemCategoryRepository->getOne($parameters);
    }

    public function getList($parameters = [])
    {
        return $this->itemCategoryRepository->getList($parameters);
    }

    public function init()
    {
        return new ItemCategory();
    }

    public function persist($data)
    {
        if ($data->getDateCreated() == null) {
            $eventName = ItemCategoryEvent::CREATED;
            $data->setDateCreated(new \Datetime());
        } else {
            $eventName = ItemCategoryEvent::UPDATED;
        }
        $data->setDateModified(new \Datetime());

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        $event = new ItemCategoryEvent($data);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new ItemCategoryEvent($data);
        $this->eventDispatcher->dispatch($event, ItemCategoryEvent::DELETED);

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        $this->clearCache();
    }
}
