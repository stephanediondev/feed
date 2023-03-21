<?php

namespace App\Manager;

use App\Entity\Enclosure;
use App\Event\EnclosureEvent;
use App\Manager\AbstractManager;
use App\Repository\EnclosureRepository;

class EnclosureManager extends AbstractManager
{
    public EnclosureRepository $enclosureRepository;

    public function __construct(
        EnclosureRepository $enclosureRepository
    ) {
        $this->enclosureRepository = $enclosureRepository;
    }

    public function getOne($parameters = []): ?Enclosure
    {
        return $this->enclosureRepository->getOne($parameters);
    }

    public function getList($parameters = [])
    {
        return $this->enclosureRepository->getList($parameters);
    }

    public function init()
    {
        return new Enclosure();
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

        $event = new EnclosureEvent($data, $mode);
        $this->eventDispatcher->dispatch($event, 'Enclosure.after_persist');

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new EnclosureEvent($data, 'delete');
        $this->eventDispatcher->dispatch($event, 'Enclosure.before_remove');

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        $this->clearCache();
    }
}
