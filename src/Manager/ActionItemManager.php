<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Action;
use App\Entity\ActionItem;
use App\Entity\Item;
use App\Entity\Member;
use App\Event\ActionItemEvent;
use App\Manager\AbstractManager;
use App\Repository\ActionItemRepository;

class ActionItemManager extends AbstractManager
{
    private ActionItemRepository $actionItemRepository;

    public function __construct(ActionItemRepository $actionItemRepository)
    {
        $this->actionItemRepository = $actionItemRepository;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?ActionItem
    {
        return $this->actionItemRepository->getOne($parameters);
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getList(array $parameters = []): mixed
    {
        return $this->actionItemRepository->getList($parameters);
    }

    public function persist(ActionItem $actionItem): void
    {
        if ($actionItem->getId() === null) {
            $eventName = ActionItemEvent::CREATED;
        } else {
            $eventName = ActionItemEvent::UPDATED;
        }

        $this->actionItemRepository->persist($actionItem);

        $event = new ActionItemEvent($actionItem);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();
    }

    public function remove(ActionItem $actionItem): void
    {
        $event = new ActionItemEvent($actionItem);
        $this->eventDispatcher->dispatch($event, ActionItemEvent::DELETED);

        $this->actionItemRepository->remove($actionItem);

        $this->clearCache();
    }

    /**
     * @return array<mixed>
     */
    public function setAction(string $case, Action $action, Item $item, ?ActionItem $actionItem, ?Member $member): array
    {
        $data = [];

        if ($actionItem) {
            $this->remove($actionItem);

            $data['action'] = $action->getReverse() ? $action->getReverse()->getTitle() : null;
            $data['action_reverse'] = $action->getTitle();

            if ($action->getReverse()) {
                if ($actionItemReverse = $this->getOne([
                    'action' => $action->getReverse(),
                    'item' => $item,
                    'member' => $member,
                ])) {
                } else {
                    $actionItemReverse = new ActionItem();
                    $actionItemReverse->setAction($action->getReverse());
                    $actionItemReverse->setItem($item);
                    $actionItemReverse->setMember($member);
                    $this->persist($actionItemReverse);
                }
            }
        } else {
            $actionItem = new ActionItem();
            $actionItem->setAction($action);
            $actionItem->setItem($item);
            $actionItem->setMember($member);
            $this->persist($actionItem);

            $data['action'] = $action->getTitle();
            $data['action_reverse'] = $action->getReverse() ? $action->getReverse()->getTitle() : null;

            if ($action->getReverse()) {
                if ($actionItemReverse = $this->getOne([
                    'action' => $action->getReverse(),
                    'item' => $item,
                    'member' => $member,
                ])) {
                    $this->remove($actionItemReverse);
                }
            }
        }

        $data['data'] = $item->getJsonApiData();

        return $data;
    }
}
