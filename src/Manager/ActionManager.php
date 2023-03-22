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

    public function persist(Action $data): int
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

    public function remove(Action $data): void
    {
        $event = new ActionEvent($data);
        $this->eventDispatcher->dispatch($event, ActionEvent::DELETED);

        $this->actionRepository->remove($data);

        $this->clearCache();
    }
}
