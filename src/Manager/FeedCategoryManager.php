<?php

namespace App\Manager;

use App\Manager\AbstractManager;
use App\Entity\FeedCategory;
use App\Event\FeedCategoryEvent;
use App\Repository\FeedCategoryRepository;

class FeedCategoryManager extends AbstractManager
{
    public FeedCategoryRepository $feedCategoryRepository;

    public function __construct(FeedCategoryRepository $feedCategoryRepository)
    {
        $this->feedCategoryRepository = $feedCategoryRepository;
    }

    public function getOne($paremeters = [])
    {
        return $this->feedCategoryRepository->getOne($paremeters);
    }

    public function getList($parameters = [])
    {
        return $this->feedCategoryRepository->getList($parameters);
    }

    public function init()
    {
        return new FeedCategory();
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

        $event = new FeedCategoryEvent($data, $mode);
        $this->eventDispatcher->dispatch($event, 'FeedCategory.after_persist');

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new FeedCategoryEvent($data, 'delete');
        $this->eventDispatcher->dispatch($event, 'FeedCategory.before_remove');

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        $this->clearCache();
    }
}
