<?php

namespace App\Manager;

use App\Entity\Connection;
use App\Event\ConnectionEvent;
use App\Manager\AbstractManager;
use App\Repository\ConnectionRepository;

class ConnectionManager extends AbstractManager
{
    public ConnectionRepository $connectionRepository;

    public function __construct(ConnectionRepository $connectionRepository)
    {
        $this->connectionRepository = $connectionRepository;
    }

    public function getOne($parameters = []): ?Connection
    {
        return $this->connectionRepository->getOne($parameters);
    }

    public function getList($parameters = [])
    {
        return $this->connectionRepository->getList($parameters);
    }

    public function init()
    {
        return new Connection();
    }

    public function persist($data)
    {
        if ($data->getDateCreated() == null) {
            $mode = 'insert';
            $data->setDateCreated(new \Datetime());
        } else {
            $mode = 'update';
        }
        $data->setDateModified(new \Datetime());

        $this->connectionRepository->persist($data);

        $event = new ConnectionEvent($data, $mode);
        $this->eventDispatcher->dispatch($event, 'Connection.after_persist');

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new ConnectionEvent($data, 'delete');
        $this->eventDispatcher->dispatch($event, 'Connection.before_remove');

        $this->connectionRepository->remove($data);

        $this->clearCache();
    }
}
