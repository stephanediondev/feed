<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;
use Readerself\CoreBundle\Entity\ActionAuthorMember;
use Readerself\CoreBundle\Event\ActionAuthorMemberEvent;

class ActionAuthorMemberManager extends AbstractManager
{
    public function getOne($paremeters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:ActionAuthorMember')->getOne($paremeters);
    }

    public function getList($parameters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:ActionAuthorMember')->getList($parameters);
    }

    public function init()
    {
        return new ActionAuthorMember();
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

        $event = new ActionAuthorMemberEvent($data, $mode);
        $this->eventDispatcher->dispatch('ActionAuthorMember.after_persist', $event);

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new ActionAuthorMemberEvent($data, 'delete');
        $this->eventDispatcher->dispatch('ActionAuthorMember.before_remove', $event);

        $this->em->remove($data);
        $this->em->flush();

        $this->clearCache();
    }
}
