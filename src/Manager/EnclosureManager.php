<?php
declare(strict_types=1);

namespace App\Manager;

use App\Entity\Enclosure;
use App\Event\EnclosureEvent;
use App\Manager\AbstractManager;
use App\Repository\EnclosureRepository;

class EnclosureManager extends AbstractManager
{
    private EnclosureRepository $enclosureRepository;

    public function __construct(EnclosureRepository $enclosureRepository)
    {
        $this->enclosureRepository = $enclosureRepository;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?Enclosure
    {
        return $this->enclosureRepository->getOne($parameters);
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getList(array $parameters = []): mixed
    {
        return $this->enclosureRepository->getList($parameters);
    }

    public function init(): Enclosure
    {
        return new Enclosure();
    }

    public function persist(Enclosure $enclosure): ?int
    {
        if ($enclosure->getDateCreated() == null) {
            $eventName = EnclosureEvent::CREATED;
            $enclosure->setDateCreated(new \Datetime());
        } else {
            $eventName = EnclosureEvent::UPDATED;
        }

        $this->enclosureRepository->persist($enclosure);

        $event = new EnclosureEvent($enclosure);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();

        return $enclosure->getId();
    }

    public function remove(Enclosure $enclosure): void
    {
        $event = new EnclosureEvent($enclosure);
        $this->eventDispatcher->dispatch($event, EnclosureEvent::DELETED);

        $this->enclosureRepository->remove($enclosure);

        $this->clearCache();
    }
}
