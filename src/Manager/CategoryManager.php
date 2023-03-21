<?php

namespace App\Manager;

use App\Entity\Category;
use App\Event\CategoryEvent;
use App\Manager\AbstractManager;
use App\Repository\CategoryRepository;

class CategoryManager extends AbstractManager
{
    private CategoryRepository $categoryRepository;

    public $itemCategoryManager;

    public $feedCategoryManager;

    public function __construct(
        CategoryRepository $categoryRepository,
        ItemCategoryManager $itemCategoryManager,
        FeedCategoryManager $feedCategoryManager
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->itemCategoryManager = $itemCategoryManager;
        $this->feedCategoryManager = $feedCategoryManager;
    }

    public function getOne($parameters = []): ?Category
    {
        return $this->categoryRepository->getOne($parameters);
    }

    public function getList($parameters = [])
    {
        return $this->categoryRepository->getList($parameters);
    }

    public function init()
    {
        return new Category();
    }

    public function persist($data)
    {
        if ($data->getDateCreated() == null) {
            $eventName = CategoryEvent::CREATED;
            $data->setDateCreated(new \Datetime());
        } else {
            $eventName = CategoryEvent::UPDATED;
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        $event = new CategoryEvent($data);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new CategoryEvent($data);
        $this->eventDispatcher->dispatch($event, CategoryEvent::DELETED);

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        $this->clearCache();
    }
}
