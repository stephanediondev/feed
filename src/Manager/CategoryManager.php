<?php

namespace App\Manager;

use App\Manager\AbstractManager;
use App\Entity\Category;
use App\Event\CategoryEvent;
use App\Repository\CategoryRepository;

class CategoryManager extends AbstractManager
{
    public CategoryRepository $categoryRepository;

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

    public function getOne($paremeters = [])
    {
        return $this->categoryRepository->getOne($paremeters);
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
            $mode = 'insert';
            $data->setDateCreated(new \Datetime());
        } else {
            $mode = 'update';
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        $event = new CategoryEvent($data, $mode);
        $this->eventDispatcher->dispatch($event, 'Category.after_persist');

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new CategoryEvent($data, 'delete');
        $this->eventDispatcher->dispatch($event, 'Category.before_remove');

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        $this->clearCache();
    }
}
