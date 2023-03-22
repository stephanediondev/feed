<?php

namespace App\Manager;

use App\Entity\ActionItem;
use App\Event\ActionItemEvent;
use App\Manager\AbstractManager;
use App\Repository\ActionItemRepository;

class ActionItemManager extends AbstractManager
{
    private ActionItemRepository $actionItemRepository;

    public function __construct(ActionItemRepository $actionItemRepository)
    {
        $this->actionItemRepository = $actionItemRepository;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?ActionItem
    {
        return $this->actionItemRepository->getOne($parameters);
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getList(array $parameters = []): mixed
    {
        return $this->actionItemRepository->getList($parameters);
    }

    public function init(): ActionItem
    {
        return new ActionItem();
    }

    public function persist(ActionItem $data): int
    {
        if ($data->getDateCreated() == null) {
            $eventName = ActionItemEvent::CREATED;
            $data->setDateCreated(new \Datetime());
        } else {
            $eventName = ActionItemEvent::UPDATED;
        }

        $this->actionItemRepository->persist($data);

        $event = new ActionItemEvent($data);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();

        return $data->getId();
    }

    public function remove(ActionItem $data): void
    {
        $event = new ActionItemEvent($data);
        $this->eventDispatcher->dispatch($event, ActionItemEvent::DELETED);

        $this->actionItemRepository->remove($data);

        $this->clearCache();
    }
}
