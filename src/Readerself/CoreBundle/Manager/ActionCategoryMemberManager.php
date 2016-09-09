<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;
use Readerself\CoreBundle\Entity\ActionCategoryMember;
use Readerself\CoreBundle\Event\ActionCategoryMemberEvent;

class ActionCategoryMemberManager extends AbstractManager
{
    public function getOne($paremeters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:ActionCategoryMember')->getOne($paremeters);
    }

    public function getList($parameters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:ActionCategoryMember')->getList($parameters);
    }

    public function init()
    {
        return new ActionCategoryMember();
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

        $event = new ActionCategoryMemberEvent($data, $mode);
        $this->eventDispatcher->dispatch('ActionCategoryMember.after_persist', $event);

        $this->removeCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new ActionCategoryMemberEvent($data, 'delete');
        $this->eventDispatcher->dispatch('ActionCategoryMember.before_remove', $event);

        $this->em->remove($data);
        $this->em->flush();

        $this->removeCache();
    }
}
