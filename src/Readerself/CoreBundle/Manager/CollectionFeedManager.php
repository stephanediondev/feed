<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;
use Readerself\CoreBundle\Entity\CollectionFeed;
use Readerself\CoreBundle\Event\CollectionFeedEvent;

class CollectionFeedManager extends AbstractManager
{
    public function getOne($paremeters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:CollectionFeed')->getOne($paremeters);
    }

    public function getList($parameters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:CollectionFeed')->getList($parameters);
    }

    public function init()
    {
        return new ActionItem();
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

        $event = new CollectionFeedEvent($data, $mode);
        $this->eventDispatcher->dispatch('CollectionFeed.after_persist', $event);

        $this->removeCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new CollectionFeedEvent($data, 'delete');
        $this->eventDispatcher->dispatch('CollectionFeed.before_remove', $event);

        $this->em->remove($data);
        $this->em->flush();

        $this->removeCache();
    }
}
