<?php
declare(strict_types=1);

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

    public function persist(ActionItem $actionItem): ?int
    {
        if ($actionItem->getDateCreated() == null) {
            $eventName = ActionItemEvent::CREATED;
            $actionItem->setDateCreated(new \Datetime());
        } else {
            $eventName = ActionItemEvent::UPDATED;
        }

        $this->actionItemRepository->persist($actionItem);

        $event = new ActionItemEvent($actionItem);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();

        return $actionItem->getId();
    }

    public function remove(ActionItem $actionItem): void
    {
        $event = new ActionItemEvent($actionItem);
        $this->eventDispatcher->dispatch($event, ActionItemEvent::DELETED);

        $this->actionItemRepository->remove($actionItem);

        $this->clearCache();
    }
}
