<?php
declare(strict_types=1);

namespace App\Manager;

use App\Entity\ActionFeed;
use App\Event\ActionFeedEvent;
use App\Manager\AbstractManager;
use App\Repository\ActionFeedRepository;

class ActionFeedManager extends AbstractManager
{
    private ActionFeedRepository $actionFeedRepository;

    public function __construct(ActionFeedRepository $actionFeedRepository)
    {
        $this->actionFeedRepository = $actionFeedRepository;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?ActionFeed
    {
        return $this->actionFeedRepository->getOne($parameters);
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getList(array $parameters = []): mixed
    {
        return $this->actionFeedRepository->getList($parameters);
    }

    public function init(): ActionFeed
    {
        return new ActionFeed();
    }

    public function persist(ActionFeed $actionFeed): ?int
    {
        if ($actionFeed->getDateCreated() == null) {
            $eventName = ActionFeedEvent::CREATED;
            $actionFeed->setDateCreated(new \Datetime());
        } else {
            $eventName = ActionFeedEvent::UPDATED;
        }

        $this->actionFeedRepository->persist($actionFeed);

        $event = new ActionFeedEvent($actionFeed);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();

        return $actionFeed->getId();
    }

    public function remove(ActionFeed $actionFeed): void
    {
        $event = new ActionFeedEvent($actionFeed);
        $this->eventDispatcher->dispatch($event, ActionFeedEvent::DELETED);

        $this->actionFeedRepository->remove($actionFeed);

        $this->clearCache();
    }
}
