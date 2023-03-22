<?php

namespace App\Manager;

use App\Entity\ActionCategory;
use App\Event\ActionCategoryEvent;
use App\Manager\AbstractManager;
use App\Repository\ActionCategoryRepository;

class ActionCategoryManager extends AbstractManager
{
    private ActionCategoryRepository $actionCategoryRepository;

    public function __construct(ActionCategoryRepository $actionCategoryRepository)
    {
        $this->actionCategoryRepository = $actionCategoryRepository;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?ActionCategory
    {
        return $this->actionCategoryRepository->getOne($parameters);
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getList(array $parameters = []): mixed
    {
        return $this->actionCategoryRepository->getList($parameters);
    }

    public function init(): ActionCategory
    {
        return new ActionCategory();
    }

    public function persist(ActionCategory $data): int
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

    public function remove(ActionCategory $data): void
    {
        $event = new ActionCategoryEvent($data);
        $this->eventDispatcher->dispatch($event, ActionCategoryEvent::DELETED);

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        $this->clearCache();
    }
}
