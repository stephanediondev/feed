<?php

namespace App\Manager;

use App\Entity\Action;
use App\Event\ActionEvent;
use App\Manager\AbstractManager;
use App\Repository\ActionRepository;

class ActionManager extends AbstractManager
{
    private ActionRepository $actionRepository;

    public function __construct(ActionRepository $actionRepository)
    {
        $this->actionRepository = $actionRepository;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?Action
    {
        return $this->actionRepository->getOne($parameters);
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getList(array $parameters = []): mixed
    {
        return $this->actionRepository->getList($parameters);
    }

    public function init(): Action
    {
        return new Action();
    }

    public function persist(Action $action): ?int
    {
        if ($action->getDateCreated() == null) {
            $eventName = ActionEvent::CREATED;
            $action->setDateCreated(new \Datetime());
        } else {
            $eventName = ActionEvent::UPDATED;
        }

        $this->actionRepository->persist($action);

        $event = new ActionEvent($action);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();

        return $action->getId();
    }

    public function remove(Action $action): void
    {
        $event = new ActionEvent($action);
        $this->eventDispatcher->dispatch($event, ActionEvent::DELETED);

        $this->actionRepository->remove($action);

        $this->clearCache();
    }
}
