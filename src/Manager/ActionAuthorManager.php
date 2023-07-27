<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Action;
use App\Entity\ActionAuthor;
use App\Entity\Author;
use App\Entity\Member;
use App\Event\ActionAuthorEvent;
use App\Manager\AbstractManager;
use App\Repository\ActionAuthorRepository;

class ActionAuthorManager extends AbstractManager
{
    private ActionAuthorRepository $actionAuthorRepository;

    public function __construct(ActionAuthorRepository $actionAuthorRepository)
    {
        $this->actionAuthorRepository = $actionAuthorRepository;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?ActionAuthor
    {
        return $this->actionAuthorRepository->getOne($parameters);
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getList(array $parameters = []): mixed
    {
        return $this->actionAuthorRepository->getList($parameters);
    }

    public function persist(ActionAuthor $actionAuthor): void
    {
        if ($actionAuthor->getId() === null) {
            $eventName = ActionAuthorEvent::CREATED;
        } else {
            $eventName = ActionAuthorEvent::UPDATED;
        }

        $this->actionAuthorRepository->persist($actionAuthor);

        $event = new ActionAuthorEvent($actionAuthor);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();
    }

    public function remove(ActionAuthor $actionAuthor): void
    {
        $event = new ActionAuthorEvent($actionAuthor);
        $this->eventDispatcher->dispatch($event, ActionAuthorEvent::DELETED);

        $this->actionAuthorRepository->remove($actionAuthor);

        $this->clearCache();
    }

    /**
     * @return array<mixed>
     */
    public function setAction(string $case, Action $action, Author $author, ?ActionAuthor $actionAuthor, ?Member $member): array
    {
        $data = [];

        if ($actionAuthor) {
            $this->remove($actionAuthor);

            $data['action'] = $action->getReverse() ? $action->getReverse()->getTitle() : null;
            $data['action_reverse'] = $action->getTitle();

            if ($action->getReverse()) {
                if ($actionAuthorReverse = $this->getOne([
                    'action' => $action->getReverse(),
                    'author' => $author,
                    'member' => $member,
                ])) {
                } else {
                    $actionAuthorReverse = new ActionAuthor();
                    $actionAuthorReverse->setAction($action->getReverse());
                    $actionAuthorReverse->setAuthor($author);
                    $actionAuthorReverse->setMember($member);
                    $this->persist($actionAuthorReverse);
                }
            }
        } else {
            $actionAuthor = new ActionAuthor();
            $actionAuthor->setAction($action);
            $actionAuthor->setAuthor($author);
            $actionAuthor->setMember($member);
            $this->persist($actionAuthor);

            $data['action'] = $action->getTitle();
            $data['action_reverse'] = $action->getReverse() ? $action->getReverse()->getTitle() : null;

            if ($action->getReverse()) {
                if ($actionAuthorReverse = $this->getOne([
                    'action' => $action->getReverse(),
                    'author' => $author,
                    'member' => $member,
                ])) {
                    $this->remove($actionAuthorReverse);
                }
            }
        }

        $data['data'] = $author->getJsonApiData();

        return $data;
    }
}
