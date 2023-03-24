<?php

namespace App\Manager;

use App\Entity\Category;
use App\Event\CategoryEvent;
use App\Manager\AbstractManager;
use App\Manager\ItemCategoryManager;
use App\Manager\FeedCategoryManager;
use App\Repository\CategoryRepository;

class CategoryManager extends AbstractManager
{
    private CategoryRepository $categoryRepository;

    public ItemCategoryManager $itemCategoryManager;

    public FeedCategoryManager $feedCategoryManager;

    public function __construct(CategoryRepository $categoryRepository, ItemCategoryManager $itemCategoryManager, FeedCategoryManager $feedCategoryManager)
    {
        $this->categoryRepository = $categoryRepository;
        $this->itemCategoryManager = $itemCategoryManager;
        $this->feedCategoryManager = $feedCategoryManager;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?Category
    {
        return $this->categoryRepository->getOne($parameters);
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getList(array $parameters = []): mixed
    {
        return $this->categoryRepository->getList($parameters);
    }

    public function init(): Category
    {
        return new Category();
    }

    public function persist(Category $data): int
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

    public function remove(Category $data): void
    {
        $event = new CategoryEvent($data);
        $this->eventDispatcher->dispatch($event, CategoryEvent::DELETED);

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        $this->clearCache();
    }
}
