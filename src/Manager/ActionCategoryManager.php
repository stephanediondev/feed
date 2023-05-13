<?php

declare(strict_types=1);

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

    public function persist(ActionCategory $actionCategory): void
    {
        if ($actionCategory->getDateCreated() === null) {
            $eventName = ActionCategoryEvent::CREATED;
            $actionCategory->setDateCreated(new \Datetime());
        } else {
            $eventName = ActionCategoryEvent::UPDATED;
        }

        $this->actionCategoryRepository->persist($actionCategory);

        $event = new ActionCategoryEvent($actionCategory);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();
    }

    public function remove(ActionCategory $actionCategory): void
    {
        $event = new ActionCategoryEvent($actionCategory);
        $this->eventDispatcher->dispatch($event, ActionCategoryEvent::DELETED);

        $this->actionCategoryRepository->remove($actionCategory);

        $this->clearCache();
    }
}
