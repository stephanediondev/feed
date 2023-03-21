<?php

namespace App\Manager;

use App\Entity\Enclosure;
use App\Event\EnclosureEvent;
use App\Manager\AbstractManager;
use App\Repository\EnclosureRepository;

class EnclosureManager extends AbstractManager
{
    private EnclosureRepository $enclosureRepository;

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
            $eventName = EnclosureEvent::CREATED;
            $data->setDateCreated(new \Datetime());
        } else {
            $eventName = EnclosureEvent::UPDATED;
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        $event = new EnclosureEvent($data);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new EnclosureEvent($data);
        $this->eventDispatcher->dispatch($event, EnclosureEvent::DELETED);

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        $this->clearCache();
    }
}
