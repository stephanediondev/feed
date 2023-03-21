<?php

namespace App\Manager;

use App\Entity\ActionItem;
use App\Event\ActionItemEvent;
use App\Manager\AbstractManager;
use App\Repository\ActionItemRepository;

class ActionItemManager extends AbstractManager
{
    public ActionItemRepository $actionItemRepository;

    public function __construct(ActionItemRepository $actionItemRepository)
    {
        $this->actionItemRepository = $actionItemRepository;
    }

    public function getOne($parameters = []): ?ActionItem
    {
        return $this->actionItemRepository->getOne($parameters);
    }

    public function getList($parameters = [])
    {
        return $this->actionItemRepository->getList($parameters);
    }

    public function init()
    {
        return new ActionItem();
    }

    public function persist($data)
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

    public function remove($data)
    {
        $event = new ActionItemEvent($data);
        $this->eventDispatcher->dispatch($event, ActionItemEvent::DELETED);

        $this->actionItemRepository->remove($data);

        $this->clearCache();
    }
}
