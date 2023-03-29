<?php
declare(strict_types=1);

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

    public function persist(Connection $connection): ?int
    {
        if ($connection->getDateCreated() == null) {
            $eventName = ConnectionEvent::CREATED;
            $connection->setDateCreated(new \Datetime());
        } else {
            $eventName = ConnectionEvent::UPDATED;
        }
        $connection->setDateModified(new \Datetime());

        $this->connectionRepository->persist($connection);

        $event = new ConnectionEvent($connection);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();

        return $connection->getId();
    }

    public function remove(Connection $connection): void
    {
        $event = new ConnectionEvent($connection);
        $this->eventDispatcher->dispatch($event, ConnectionEvent::DELETED);

        $this->connectionRepository->remove($connection);

        $this->clearCache();
    }
}
