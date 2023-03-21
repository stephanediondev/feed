<?php

namespace App\Manager;

use App\Entity\ActionFeed;
use App\Event\ActionFeedEvent;
use App\Manager\AbstractManager;
use App\Repository\ActionFeedRepository;

class ActionFeedManager extends AbstractManager
{
    public ActionFeedRepository $actionFeedRepository;

    public function __construct(ActionFeedRepository $actionFeedRepository)
    {
        $this->actionFeedRepository = $actionFeedRepository;
    }

    public function getOne($parameters = []): ?ActionFeed
    {
        return $this->actionFeedRepository->getOne($parameters);
    }

    public function getList($parameters = [])
    {
        return $this->actionFeedRepository->getList($parameters);
    }

    public function init()
    {
        return new ActionFeed();
    }

    public function persist($data)
    {
        if ($data->getDateCreated() == null) {
            $eventName = ActionFeedEvent::CREATED;
            $data->setDateCreated(new \Datetime());
        } else {
            $eventName = ActionFeedEvent::UPDATED;
        }

        $this->actionFeedRepository->persist($data);

        $event = new ActionFeedEvent($data);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new ActionFeedEvent($data);
        $this->eventDispatcher->dispatch($event, ActionFeedEvent::DELETED);

        $this->actionFeedRepository->remove($data);

        $this->clearCache();
    }
}
