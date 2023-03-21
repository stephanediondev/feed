<?php

namespace App\Manager;

use App\Entity\Action;
use App\Event\ActionEvent;
use App\Manager\AbstractManager;
use App\Repository\ActionRepository;

class ActionManager extends AbstractManager
{
    public ActionRepository $actionRepository;

    public function __construct(ActionRepository $actionRepository)
    {
        $this->actionRepository = $actionRepository;
    }

    public function getOne($parameters = []): ?Action
    {
        return $this->actionRepository->getOne($parameters);
    }

    public function getList($parameters = [])
    {
        return $this->actionRepository->getList($parameters);
    }

    public function init()
    {
        return new Action();
    }

    public function persist($data)
    {
        if ($data->getDateCreated() == null) {
            $eventName = ActionEvent::CREATED;
            $data->setDateCreated(new \Datetime());
        } else {
            $eventName = ActionEvent::UPDATED;
        }

        $this->actionRepository->persist($data);

        $event = new ActionEvent($data);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new ActionEvent($data);
        $this->eventDispatcher->dispatch($event, ActionEvent::DELETED);

        $this->actionRepository->remove($data);

        $this->clearCache();
    }
}