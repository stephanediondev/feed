<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;
use Readerself\CoreBundle\Entity\Category;
use Readerself\CoreBundle\Event\CategoryEvent;

class CategoryManager extends AbstractManager
{
    public $itemCategoryManager;

    public $feedCategoryManager;

    public function __construct(
        ItemCategoryManager $itemCategoryManager,
        FeedCategoryManager $feedCategoryManager
    ) {
        $this->itemCategoryManager = $itemCategoryManager;
        $this->feedCategoryManager = $feedCategoryManager;
    }

    public function getOne($paremeters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:Category')->getOne($paremeters);
    }

    public function getList($parameters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:Category')->getList($parameters);
    }

    public function init()
    {
        return new Category();
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

        $event = new CategoryEvent($data, $mode);
        $this->eventDispatcher->dispatch('Category.after_persist', $event);

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new CategoryEvent($data, 'delete');
        $this->eventDispatcher->dispatch('Category.before_remove', $event);

        $this->em->remove($data);
        $this->em->flush();

        $this->clearCache();
    }
}
