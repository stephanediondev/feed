<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;
use Readerself\CoreBundle\Entity\Author;
use Readerself\CoreBundle\Event\AuthorEvent;

class AuthorManager extends AbstractManager
{
    public function getOne($paremeters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:Author')->getOne($paremeters);
    }

    public function getList($parameters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:Author')->getList($parameters);
    }

    public function init()
    {
        return new Author();
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

        $event = new AuthorEvent($data, $mode);
        $this->eventDispatcher->dispatch('Author.after_persist', $event);

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new AuthorEvent($data, 'delete');
        $this->eventDispatcher->dispatch('Author.before_remove', $event);

        $this->em->remove($data);
        $this->em->flush();

        $this->clearCache();
    }
}
