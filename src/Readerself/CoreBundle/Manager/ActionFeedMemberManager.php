<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;
use Readerself\CoreBundle\Entity\ActionFeedMember;
use Readerself\CoreBundle\Event\ActionFeedMemberEvent;

class ActionFeedMemberManager extends AbstractManager
{
    public function getOne($paremeters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:ActionFeedMember')->getOne($paremeters);
    }

    public function getList($parameters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:ActionFeedMember')->getList($parameters);
    }

    public function init()
    {
        return new ActionFeedMember();
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

        $event = new ActionFeedMemberEvent($data, $mode);
        $this->eventDispatcher->dispatch('ActionFeedMember.after_persist', $event);

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new ActionFeedMemberEvent($data, 'delete');
        $this->eventDispatcher->dispatch('ActionFeedMember.before_remove', $event);

        $this->em->remove($data);
        $this->em->flush();

        $this->clearCache();
    }
}
