<?php

namespace App\Manager;

use App\Entity\Author;
use App\Event\AuthorEvent;
use App\Manager\AbstractManager;
use App\Repository\AuthorRepository;

class AuthorManager extends AbstractManager
{
    private AuthorRepository $authorRepository;

    public function __construct(
        AuthorRepository $authorRepository
    ) {
        $this->authorRepository = $authorRepository;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?Author
    {
        return $this->authorRepository->getOne($parameters);
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getList(array $parameters = []): mixed
    {
        return $this->authorRepository->getList($parameters);
    }

    public function init(): Author
    {
        return new Author();
    }

    public function persist(Author $data): int
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

    public function remove(Author $data): void
    {
        $event = new AuthorEvent($data);
        $this->eventDispatcher->dispatch($event, AuthorEvent::DELETED);

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        $this->clearCache();
    }
}
