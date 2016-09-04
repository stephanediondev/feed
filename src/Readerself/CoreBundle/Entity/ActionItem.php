<?php

namespace Readerself\CoreBundle\Entity;

/**
 * ActionItem
 */
class ActionItem
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Readerself\CoreBundle\Entity\Item
     */
    private $item;

    /**
     * @var \Readerself\CoreBundle\Entity\Action
     */
    private $action;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set item
     *
     * @param \Readerself\CoreBundle\Entity\Item $item
     *
     * @return ActionItem
     */
    public function setItem(\Readerself\CoreBundle\Entity\Item $item = null)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get item
     *
     * @return \Readerself\CoreBundle\Entity\Item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set action
     *
     * @param \Readerself\CoreBundle\Entity\Action $action
     *
     * @return ActionItem
     */
    public function setAction(\Readerself\CoreBundle\Entity\Action $action = null)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return \Readerself\CoreBundle\Entity\Action
     */
    public function getAction()
    {
        return $this->action;
    }
}

