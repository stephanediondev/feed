<?php

namespace App\Manager;

use App\Entity\Author;
use App\Event\AuthorEvent;
use App\Manager\AbstractManager;
use App\Repository\AuthorRepository;

class AuthorManager extends AbstractManager
{
    private AuthorRepository $authorRepository;

    public function __construct(AuthorRepository $authorRepository)
    {
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

    public function persist(Author $author): ?int
    {
        if ($author->getDateCreated() == null) {
            $eventName = AuthorEvent::CREATED;
            $author->setDateCreated(new \Datetime());
        } else {
            $eventName = AuthorEvent::UPDATED;
        }

        $this->authorRepository->persist($author);

        $event = new AuthorEvent($author);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();

        return $author->getId();
    }

    public function remove(Author $author): void
    {
        $event = new AuthorEvent($author);
        $this->eventDispatcher->dispatch($event, AuthorEvent::DELETED);

        $this->authorRepository->remove($author);

        $this->clearCache();
    }
}
