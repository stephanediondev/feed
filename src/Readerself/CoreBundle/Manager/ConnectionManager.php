<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;
use Readerself\CoreBundle\Entity\Connection;
use Readerself\CoreBundle\Event\ConnectionEvent;

class ConnectionManager extends AbstractManager
{
    public function getOne($paremeters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:Connection')->getOne($paremeters);
    }

    public function getList($parameters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:Connection')->getList($parameters);
    }

    public function init()
    {
        return new Feed();
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

        $event = new ConnectionEvent($data, $mode);
        $this->eventDispatcher->dispatch('connection.after_persist', $event);

        $this->removeCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new ConnectionEvent($data, 'delete');
        $this->eventDispatcher->dispatch('connection.before_remove', $event);

        $this->em->remove($data);
        $this->em->flush();

        $this->removeCache();
    }
}
