<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;
use Readerself\CoreBundle\Entity\Action;
use Readerself\CoreBundle\Event\ActionEvent;

class ActionManager extends AbstractManager
{
    public $actionItemManager;

    public $actionItemMemberManager;

    public $actionCategoryMemberManager;

    public function __construct(
        ActionItemManager $actionItemManager,
        ActionItemMemberManager $actionItemMemberManager,
        ActionCategoryMemberManager $actionCategoryMemberManager
    ) {
        $this->actionItemManager = $actionItemManager;
        $this->actionItemMemberManager = $actionItemMemberManager;
        $this->actionCategoryMemberManager = $actionCategoryMemberManager;
    }

    public function getOne($paremeters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:Action')->getOne($paremeters);
    }

    public function getList($parameters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:Action')->getList($parameters);
    }

    public function init()
    {
        return new Action();
    }

    public function persist($data)
    {
        if($data->getDateCreated() == null) {
            $mode = 'insert';
            $data->setDateCreated(new \Datetime());
        } else {
            $mode = 'update';
        }

        $this->em->persist($data);
        $this->em->flush();

        $event = new ActionEvent($data, $mode);
        $this->eventDispatcher->dispatch('Action.after_persist', $event);

        $this->removeCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new ActionEvent($data, 'delete');
        $this->eventDispatcher->dispatch('Action.before_remove', $event);

        $this->em->remove($data);
        $this->em->flush();

        $this->removeCache();
    }
}
