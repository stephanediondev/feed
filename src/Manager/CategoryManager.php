<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Category;
use App\Event\CategoryEvent;
use App\Manager\AbstractManager;
use App\Manager\FeedCategoryManager;
use App\Manager\ItemCategoryManager;
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

    public function persist(Category $category): void
    {
        if ($category->getDateCreated() === null) {
            $eventName = CategoryEvent::CREATED;
            $category->setDateCreated(new \Datetime());
        } else {
            $eventName = CategoryEvent::UPDATED;
        }

        $this->categoryRepository->persist($category);

        $event = new CategoryEvent($category);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();
    }

    public function remove(Category $category): void
    {
        $event = new CategoryEvent($category);
        $this->eventDispatcher->dispatch($event, CategoryEvent::DELETED);

        $this->categoryRepository->remove($category);

        $this->clearCache();
    }
}
