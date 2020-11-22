<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;
use Readerself\CoreBundle\Entity\ActionFeed;
use Readerself\CoreBundle\Event\ActionFeedEvent;

class ActionFeedManager extends AbstractManager
{
    public function getOne($paremeters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:ActionFeed')->getOne($paremeters);
    }

    public function getList($parameters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:ActionFeed')->getList($parameters);
    }

    public function init()
    {
        return new ActionFeed();
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

        $event = new ActionFeedEvent($data, $mode);
        $this->eventDispatcher->dispatch('ActionFeed.after_persist', $event);

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new ActionFeedEvent($data, 'delete');
        $this->eventDispatcher->dispatch('ActionFeed.before_remove', $event);

        $this->em->remove($data);
        $this->em->flush();

        $this->clearCache();
    }
}
