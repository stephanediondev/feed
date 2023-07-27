<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Action;
use App\Entity\ActionFeed;
use App\Entity\Feed;
use App\Entity\Member;
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

    public function persist(ActionFeed $actionFeed): void
    {
        if ($actionFeed->getId() === null) {
            $eventName = ActionFeedEvent::CREATED;
        } else {
            $eventName = ActionFeedEvent::UPDATED;
        }

        $this->actionFeedRepository->persist($actionFeed);

        $event = new ActionFeedEvent($actionFeed);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();
    }

    public function remove(ActionFeed $actionFeed): void
    {
        $event = new ActionFeedEvent($actionFeed);
        $this->eventDispatcher->dispatch($event, ActionFeedEvent::DELETED);

        $this->actionFeedRepository->remove($actionFeed);

        $this->clearCache();
    }

    /**
     * @return array<mixed>
     */
    public function setAction(string $case, Action $action, Feed $feed, ?ActionFeed $actionFeed, ?Member $member): array
    {
        $data = [];

        if ($actionFeed) {
            $this->remove($actionFeed);

            $data['action'] = $action->getReverse() ? $action->getReverse()->getTitle() : null;
            $data['action_reverse'] = $action->getTitle();

            if ($action->getReverse()) {
                if ($actionFeedReverse = $this->getOne([
                    'action' => $action->getReverse(),
                    'feed' => $feed,
                    'member' => $member,
                ])) {
                } else {
                    $actionFeedReverse = new ActionFeed();
                    $actionFeedReverse->setAction($action->getReverse());
                    $actionFeedReverse->setFeed($feed);
                    $actionFeedReverse->setMember($member);
                    $this->persist($actionFeedReverse);
                }
            }
        } else {
            $actionFeed = new ActionFeed();
            $actionFeed->setAction($action);
            $actionFeed->setFeed($feed);
            $actionFeed->setMember($member);
            $this->persist($actionFeed);

            $data['action'] = $action->getTitle();
            $data['action_reverse'] = $action->getReverse() ? $action->getReverse()->getTitle() : null;

            if ($action->getReverse()) {
                if ($actionFeedReverse = $this->getOne([
                    'action' => $action->getReverse(),
                    'feed' => $feed,
                    'member' => $member,
                ])) {
                    $this->remove($actionFeedReverse);
                }
            }
        }

        $data['data'] = $feed->getJsonApiData();

        return $data;
    }
}
