<?php

namespace App\Manager;

use App\Manager\AbstractManager;
use App\Entity\ActionAuthor;
use App\Event\ActionAuthorEvent;
use App\Repository\ActionAuthorRepository;

class ActionAuthorManager extends AbstractManager
{
    public ActionAuthorRepository $actionAuthorRepository;

    public function __construct(ActionAuthorRepository $actionAuthorRepository)
    {
        $this->actionAuthorRepository = $actionAuthorRepository;
    }

    public function getOne($paremeters = [])
    {
        return $this->actionAuthorRepository->getOne($paremeters);
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
            $mode = 'insert';
            $data->setDateCreated(new \Datetime());
        } else {
            $mode = 'update';
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        $event = new ActionAuthorEvent($data, $mode);
        $this->eventDispatcher->dispatch($event, 'ActionAuthor.after_persist');

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new ActionAuthorEvent($data, 'delete');
        $this->eventDispatcher->dispatch($event, 'ActionAuthor.before_remove');

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        $this->clearCache();
    }
}
