<?php

namespace App\Manager;

use App\Entity\ActionCategory;
use App\Event\ActionCategoryEvent;
use App\Manager\AbstractManager;
use App\Repository\ActionCategoryRepository;

class ActionCategoryManager extends AbstractManager
{
    public ActionCategoryRepository $actionCategoryRepository;

    public function __construct(ActionCategoryRepository $actionCategoryRepository)
    {
        $this->actionCategoryRepository = $actionCategoryRepository;
    }

    public function getOne($parameters = []): ?ActionCategory
    {
        return $this->actionCategoryRepository->getOne($parameters);
    }

    public function getList($parameters = [])
    {
        return $this->actionCategoryRepository->getList($parameters);
    }

    public function init()
    {
        return new ActionCategory();
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

        $event = new ActionCategoryEvent($data, $mode);
        $this->eventDispatcher->dispatch($event, 'ActionCategory.after_persist');

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new ActionCategoryEvent($data, 'delete');
        $this->eventDispatcher->dispatch($event, 'ActionCategory.before_remove');

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        $this->clearCache();
    }
}
