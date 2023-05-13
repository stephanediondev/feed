<?php

declare(strict_types=1);

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

    public function persist(ActionAuthor $actionAuthor): void
    {
        if ($actionAuthor->getDateCreated() === null) {
            $eventName = ActionAuthorEvent::CREATED;
            $actionAuthor->setDateCreated(new \Datetime());
        } else {
            $eventName = ActionAuthorEvent::UPDATED;
        }

        $this->actionAuthorRepository->persist($actionAuthor);

        $event = new ActionAuthorEvent($actionAuthor);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();
    }

    public function remove(ActionAuthor $actionAuthor): void
    {
        $event = new ActionAuthorEvent($actionAuthor);
        $this->eventDispatcher->dispatch($event, ActionAuthorEvent::DELETED);

        $this->actionAuthorRepository->remove($actionAuthor);

        $this->clearCache();
    }
}
