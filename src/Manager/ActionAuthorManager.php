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

    public function getOne($parameters = []): ?ActionAuthor
    {
        return $this->actionAuthorRepository->getOne($parameters);
    }

    public function getList($parameters = [])
    {
        return $this->actionAuthorRepository->getList($parameters);
    }

    public function init()
    {
        return new ActionAuthor();
    }

    public function persist($data)
    {
        if ($data->getDateCreated() == null) {
            $eventName = ActionAuthorEvent::CREATED;
            $data->setDateCreated(new \Datetime());
        } else {
            $eventName = ActionAuthorEvent::UPDATED;
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        $event = new ActionAuthorEvent($data);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new ActionAuthorEvent($data);
        $this->eventDispatcher->dispatch($event, ActionAuthorEvent::DELETED);

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        $this->clearCache();
    }
}
