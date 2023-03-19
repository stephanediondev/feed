<?php

namespace App\Manager;

use App\Manager\AbstractManager;
use App\Entity\ActionItem;
use App\Event\ActionItemEvent;
use App\Repository\ActionItemRepository;

class ActionItemManager extends AbstractManager
{
    public ActionItemRepository $actionItemRepository;

    public function __construct(ActionItemRepository $actionItemRepository)
    {
        $this->actionItemRepository = $actionItemRepository;
    }

    public function getOne($paremeters = [])
    {
        return $this->actionItemRepository->getOne($paremeters);
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
            $mode = 'insert';
            $data->setDateCreated(new \Datetime());
        } else {
            $mode = 'update';
        }

        $this->actionItemRepository->persist($data);

        $event = new ActionItemEvent($data, $mode);
        $this->eventDispatcher->dispatch($event, 'ActionItem.after_persist');

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new ActionItemEvent($data, 'delete');
        $this->eventDispatcher->dispatch($event, 'ActionItem.before_remove');

        $this->actionItemRepository->remove($data);

        $this->clearCache();
    }
}
