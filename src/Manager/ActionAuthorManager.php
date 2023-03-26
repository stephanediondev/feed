<?php

namespace App\Manager;

use App\Entity\ActionAuthor;
use App\Event\ActionAuthorEvent;
use App\Manager\AbstractManager;
use App\Repository\ActionAuthorRepository;

class ActionAuthorManager extends AbstractManager
{
    private ActionAuthorRepository $actionAuthorRepository;

    public function __construct(ActionAuthorRepository $actionAuthorRepository)
    {
        $this->actionAuthorRepository = $actionAuthorRepository;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?ActionAuthor
    {
        return $this->actionAuthorRepository->getOne($parameters);
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getList(array $parameters = []): mixed
    {
        return $this->actionAuthorRepository->getList($parameters);
    }

    public function init(): ActionAuthor
    {
        return new ActionAuthor();
    }

    public function persist(ActionAuthor $data): ?int
    {
        if ($data->getDateCreated() == null) {
            $eventName = ActionAuthorEvent::CREATED;
            $data->setDateCreated(new \Datetime());
        } else {
            $eventName = ActionAuthorEvent::UPDATED;
        }

        $this->actionAuthorRepository->persist($data);

        $event = new ActionAuthorEvent($data);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();

        return $data->getId();
    }

    public function remove(ActionAuthor $data): void
    {
        $event = new ActionAuthorEvent($data);
        $this->eventDispatcher->dispatch($event, ActionAuthorEvent::DELETED);

        $this->actionAuthorRepository->remove($data);

        $this->clearCache();
    }
}
