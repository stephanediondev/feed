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
            $mode = 'insert';
            $data->setDateCreated(new \Datetime());
        } else {
            $mode = 'update';
        }

        $this->actionFeedRepository->persist($data);

        $event = new ActionFeedEvent($data, $mode);
        $this->eventDispatcher->dispatch($event, 'ActionFeed.after_persist');

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new ActionFeedEvent($data, 'delete');
        $this->eventDispatcher->dispatch($event, 'ActionFeed.before_remove');

        $this->actionFeedRepository->remove($data);

        $this->clearCache();
    }
}
