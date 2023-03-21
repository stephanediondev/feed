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
            $mode = 'insert';
            $data->setDateCreated(new \Datetime());
        } else {
            $mode = 'update';
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        $event = new AuthorEvent($data, $mode);
        $this->eventDispatcher->dispatch($event, 'Author.after_persist');

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new AuthorEvent($data, 'delete');
        $this->eventDispatcher->dispatch($event, 'Author.before_remove');

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        $this->clearCache();
    }
}
