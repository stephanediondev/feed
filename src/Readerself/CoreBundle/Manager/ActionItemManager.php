<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;
use Readerself\CoreBundle\Entity\ActionItem;
use Readerself\CoreBundle\Event\ActionItemEvent;

class ActionItemManager extends AbstractManager
{
    public function getOne($paremeters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:ActionItem')->getOne($paremeters);
    }

    public function getList($parameters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:ActionItem')->getList($parameters);
    }

    public function init()
    {
        return new ActionItem();
    }

    public function persist($data)
    {
        if($data->getDateCreated() === null) {
            $mode = 'insert';
            $data->setDateCreated(new \Datetime());
        } else {
            $mode = 'update';
        }

        $this->em->persist($data);
        $this->em->flush();

        $event = new ActionItemEvent($data, $mode);
        $this->eventDispatcher->dispatch('ActionItem.after_persist', $event);

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new ActionItemEvent($data, 'delete');
        $this->eventDispatcher->dispatch('ActionItem.before_remove', $event);

        $this->em->remove($data);
        $this->em->flush();

        $this->clearCache();
    }
}
