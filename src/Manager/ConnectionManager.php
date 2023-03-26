<?php

namespace App\Manager;

use App\Entity\Connection;
use App\Event\ConnectionEvent;
use App\Manager\AbstractManager;
use App\Repository\ConnectionRepository;

class ConnectionManager extends AbstractManager
{
    private ConnectionRepository $connectionRepository;

    public function __construct(ConnectionRepository $connectionRepository)
    {
        $this->connectionRepository = $connectionRepository;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?Connection
    {
        return $this->connectionRepository->getOne($parameters);
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getList(array $parameters = []): mixed
    {
        return $this->connectionRepository->getList($parameters);
    }

    public function init(): Connection
    {
        return new Connection();
    }

    public function persist(Connection $data): ?int
    {
        if ($data->getDateCreated() == null) {
            $eventName = ConnectionEvent::CREATED;
            $data->setDateCreated(new \Datetime());
        } else {
            $eventName = ConnectionEvent::UPDATED;
        }
        $data->setDateModified(new \Datetime());

        $this->connectionRepository->persist($data);

        $event = new ConnectionEvent($data);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();

        return $data->getId();
    }

    public function remove(Connection $data): void
    {
        $event = new ConnectionEvent($data);
        $this->eventDispatcher->dispatch($event, ConnectionEvent::DELETED);

        $this->connectionRepository->remove($data);

        $this->clearCache();
    }
}
