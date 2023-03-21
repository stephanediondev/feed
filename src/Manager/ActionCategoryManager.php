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
            $eventName = ActionCategoryEvent::CREATED;
            $data->setDateCreated(new \Datetime());
        } else {
            $eventName = ActionCategoryEvent::UPDATED;
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        $event = new ActionCategoryEvent($data);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new ActionCategoryEvent($data);
        $this->eventDispatcher->dispatch($event, ActionCategoryEvent::DELETED);

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        $this->clearCache();
    }
}
