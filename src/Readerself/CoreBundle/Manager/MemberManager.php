<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;
use Readerself\CoreBundle\Entity\Member;
use Readerself\CoreBundle\Event\MemberEvent;

use Readerself\CoreBundle\Manager\ConnectionManager;

class MemberManager extends AbstractManager
{
    public $connectionManager;

    public $pushManager;

    public $folderManager;

    public function __construct(
        ConnectionManager $connectionManager,
        PushManager $pushManager,
        FolderManager $folderManager
    ) {
        $this->connectionManager = $connectionManager;
        $this->pushManager = $pushManager;
        $this->folderManager = $folderManager;
    }

    public function getOne($paremeters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:Member')->getOne($paremeters);
    }

    public function getList($parameters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:Member')->getList($parameters);
    }

    public function init()
    {
        return new Member();
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

        $event = new MemberEvent($data, $mode);
        $this->eventDispatcher->dispatch('Member.after_persist', $event);

        $this->removeCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new MemberEvent($data, 'delete');
        $this->eventDispatcher->dispatch('Member.before_remove', $event);

        $this->em->remove($data);
        $this->em->flush();

        $this->removeCache();
    }
}
