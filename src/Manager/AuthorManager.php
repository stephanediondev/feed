<?php

namespace App\Manager;

use App\Entity\Author;
use App\Event\AuthorEvent;
use App\Manager\AbstractManager;
use App\Repository\AuthorRepository;

class AuthorManager extends AbstractManager
{
    public AuthorRepository $authorRepository;

    public function __construct(
        AuthorRepository $authorRepository
    ) {
        $this->authorRepository = $authorRepository;
    }

    public function getOne($parameters = []): ?Author
    {
        return $this->authorRepository->getOne($parameters);
    }

    public function getList($parameters = [])
    {
        return $this->authorRepository->getList($parameters);
    }

    public function init()
    {
        return new Author();
    }

    public function persist($data)
    {
        if ($data->getDateCreated() == null) {
            $eventName = AuthorEvent::CREATED;
            $data->setDateCreated(new \Datetime());
        } else {
            $eventName = AuthorEvent::UPDATED;
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        $event = new AuthorEvent($data);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new AuthorEvent($data);
        $this->eventDispatcher->dispatch($event, AuthorEvent::DELETED);

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        $this->clearCache();
    }
}
