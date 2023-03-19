<?php

namespace App\Manager;

use App\Manager\AbstractManager;
use App\Entity\ItemCategory;
use App\Event\ItemCategoryEvent;
use App\Repository\ItemCategoryRepository;

class ItemCategoryManager extends AbstractManager
{
    public ItemCategoryRepository $itemCategoryRepository;

    public function __construct(ItemCategoryRepository $itemCategoryRepository)
    {
        $this->itemCategoryRepository = $itemCategoryRepository;
    }

    public function getOne($paremeters = [])
    {
        return $this->itemCategoryRepository->getOne($paremeters);
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
            $mode = 'insert';
            $data->setDateCreated(new \Datetime());
        } else {
            $mode = 'update';
        }
        $data->setDateModified(new \Datetime());

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        $event = new ItemCategoryEvent($data, $mode);
        $this->eventDispatcher->dispatch($event, 'ItemCategory.after_persist');

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new ItemCategoryEvent($data, 'delete');
        $this->eventDispatcher->dispatch($event, 'ItemCategory.before_remove');

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        $this->clearCache();
    }
}
