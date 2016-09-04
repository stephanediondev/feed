<?php

namespace Readerself\CoreBundle\Entity;

/**
 * ActionCategory
 */
class ActionCategory
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Readerself\CoreBundle\Entity\Category
     */
    private $category;

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
     * Set category
     *
     * @param \Readerself\CoreBundle\Entity\Category $category
     *
     * @return ActionCategory
     */
    public function setCategory(\Readerself\CoreBundle\Entity\Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \Readerself\CoreBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set action
     *
     * @param \Readerself\CoreBundle\Entity\Action $action
     *
     * @return ActionCategory
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

