<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;
use Readerself\CoreBundle\Entity\ActionItemMember;
use Readerself\CoreBundle\Event\ActionItemMemberEvent;

class ActionItemMemberManager extends AbstractManager
{
    public function getOne($paremeters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:ActionItemMember')->getOne($paremeters);
    }

    public function getList($parameters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:ActionItemMember')->getList($parameters);
    }

    public function init()
    {
        return new ActionItemMember();
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

        $event = new ActionItemMemberEvent($data, $mode);
        $this->eventDispatcher->dispatch('ActionItemMember.after_persist', $event);

        $this->removeCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new ActionItemMemberEvent($data, 'delete');
        $this->eventDispatcher->dispatch('ActionItemMember.before_remove', $event);

        $this->em->remove($data);
        $this->em->flush();

        $this->removeCache();
    }
}
