<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;
use Readerself\CoreBundle\Entity\Feed;
use Readerself\CoreBundle\Event\FeedEvent;

class FeedManager extends AbstractManager
{
    public function getOne($paremeters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:Feed')->getOne($paremeters);
    }

    public function getList($parameters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:Feed')->getList($parameters);
    }

    public function persist($data)
    {
        if($data->getDateCreated() == null) {
            $mode = 'insert';
            $data->setDateCreated(new \Datetime());
        } else {
            $mode = 'update';
        }
        $data->setDateModified(new \Datetime());

        $this->em->persist($data);
        $this->em->flush();

        $event = new FeedEvent($data, $mode);
        $this->eventDispatcher->dispatch('feed.after_persist', $event);

        $this->removeCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new FeedEvent($data, 'delete');
        $this->eventDispatcher->dispatch('feed.before_remove', $event);

        $this->em->remove($data);
        $this->em->flush();

        $this->removeCache();
    }
}
