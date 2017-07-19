<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;
use Readerself\CoreBundle\Entity\Connection;
use Readerself\CoreBundle\Event\ConnectionEvent;
use Readerself\CoreBundle\Entity\Member;

use Symfony\Component\HttpFoundation\Request;

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
        return new Connection();
    }

    public function persist($data)
    {
        if($data->getDateCreated() === null) {
            $mode = 'insert';
            $data->setDateCreated(new \Datetime());
        } else {
            $mode = 'update';
        }
        $data->setDateModified(new \Datetime());

        $this->em->persist($data);
        $this->em->flush();

        $event = new ConnectionEvent($data, $mode);
        $this->eventDispatcher->dispatch('Connection.after_persist', $event);

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new ConnectionEvent($data, 'delete');
        $this->eventDispatcher->dispatch('Connection.before_remove', $event);

        $this->em->remove($data);
        $this->em->flush();

        $this->clearCache();
    }

    public function getConnections(Member $memberConnected, Request $request) {
        $connections = [];
        $index = 0;
        foreach($this->getList(['member' => $memberConnected])->getResult() as $connection) {
            $connections[$index] = $connection->toArray();
            if($connection->getIp() == $request->getClientIp()) {
                $connections[$index]['currentIp'] = true;
            }
            if($connection->getAgent() == $request->server->get('HTTP_USER_AGENT')) {
                $connections[$index]['currentAgent'] = true;
            }
            $index++;
        }
        return $connections;
    }
}
