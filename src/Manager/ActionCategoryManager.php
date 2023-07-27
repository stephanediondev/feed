<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Action;
use App\Entity\ActionCategory;
use App\Entity\Category;
use App\Entity\Member;
use App\Event\ActionCategoryEvent;
use App\Manager\AbstractManager;
use App\Repository\ActionCategoryRepository;

class ActionCategoryManager extends AbstractManager
{
    private ActionCategoryRepository $actionCategoryRepository;

    public function __construct(ActionCategoryRepository $actionCategoryRepository)
    {
        $this->actionCategoryRepository = $actionCategoryRepository;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?ActionCategory
    {
        return $this->actionCategoryRepository->getOne($parameters);
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getList(array $parameters = []): mixed
    {
        return $this->actionCategoryRepository->getList($parameters);
    }

    public function persist(ActionCategory $actionCategory): void
    {
        if ($actionCategory->getId() === null) {
            $eventName = ActionCategoryEvent::CREATED;
        } else {
            $eventName = ActionCategoryEvent::UPDATED;
        }

        $this->actionCategoryRepository->persist($actionCategory);

        $event = new ActionCategoryEvent($actionCategory);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();
    }

    public function remove(ActionCategory $actionCategory): void
    {
        $event = new ActionCategoryEvent($actionCategory);
        $this->eventDispatcher->dispatch($event, ActionCategoryEvent::DELETED);

        $this->actionCategoryRepository->remove($actionCategory);

        $this->clearCache();
    }

    /**
     * @return array<mixed>
     */
    public function setAction(string $case, Action $action, Category $category, ?ActionCategory $actionCategory, ?Member $member): array
    {
        $data = [];

        if ($actionCategory) {
            $this->remove($actionCategory);

            $data['action'] = $action->getReverse() ? $action->getReverse()->getTitle() : null;
            $data['action_reverse'] = $action->getTitle();

            if ($action->getReverse()) {
                if ($actionCategoryReverse = $this->getOne([
                    'action' => $action->getReverse(),
                    'category' => $category,
                    'member' => $member,
                ])) {
                } else {
                    $actionCategoryReverse = new ActionCategory();
                    $actionCategoryReverse->setAction($action->getReverse());
                    $actionCategoryReverse->setCategory($category);
                    $actionCategoryReverse->setMember($member);
                    $this->persist($actionCategoryReverse);
                }
            }
        } else {
            $actionCategory = new ActionCategory();
            $actionCategory->setAction($action);
            $actionCategory->setCategory($category);
            $actionCategory->setMember($member);
            $this->persist($actionCategory);

            $data['action'] = $action->getTitle();
            $data['action_reverse'] = $action->getReverse() ? $action->getReverse()->getTitle() : null;

            if ($action->getReverse()) {
                if ($actionCategoryReverse = $this->getOne([
                    'action' => $action->getReverse(),
                    'category' => $category,
                    'member' => $member,
                ])) {
                    $this->remove($actionCategoryReverse);
                }
            }
        }

        $data['data'] = $category->getJsonApiData();

        return $data;
    }
}
