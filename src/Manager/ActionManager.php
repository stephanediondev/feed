<?php

namespace App\Manager;

use App\Entity\Action;
use App\Event\ActionEvent;
use App\Manager\AbstractManager;
use App\Repository\ActionRepository;

class ActionManager extends AbstractManager
{
    public ActionRepository $actionRepository;

    public $actionItemManager;

    public $actionFeedManager;

    public $actionCategoryManager;

    public $actionAuthorManager;

    public function __construct(
        ActionRepository $actionRepository,
        ActionItemManager $actionItemManager,
        ActionFeedManager $actionFeedManager,
        ActionCategoryManager $actionCategoryManager,
        ActionAuthorManager $actionAuthorManager
    ) {
        $this->actionRepository = $actionRepository;
        $this->actionItemManager = $actionItemManager;
        $this->actionFeedManager = $actionFeedManager;
        $this->actionCategoryManager = $actionCategoryManager;
        $this->actionAuthorManager = $actionAuthorManager;
    }

    public function getOne($parameters = []): ?Action
    {
        return $this->actionRepository->getOne($parameters);
    }

    public function getList($parameters = [])
    {
        return $this->actionRepository->getList($parameters);
    }

    public function init()
    {
        return new Action();
    }

    public function persist($data)
    {
        if ($data->getDateCreated() == null) {
            $mode = 'insert';
            $data->setDateCreated(new \Datetime());
        } else {
            $mode = 'update';
        }

        $this->actionRepository->persist($data);

        $event = new ActionEvent($data, $mode);
        $this->eventDispatcher->dispatch($event, 'Action.after_persist');

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new ActionEvent($data, 'delete');
        $this->eventDispatcher->dispatch($event, 'Action.before_remove');

        $this->actionRepository->remove($data);

        $this->clearCache();
    }
}
