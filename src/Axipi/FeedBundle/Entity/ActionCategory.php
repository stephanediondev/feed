<?php

namespace Axipi\FeedBundle\Entity;

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
     * @var \Axipi\FeedBundle\Entity\Category
     */
    private $category;

    /**
     * @var \Axipi\FeedBundle\Entity\Action
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
     * @param \Axipi\FeedBundle\Entity\Category $category
     *
     * @return ActionCategory
     */
    public function setCategory(\Axipi\FeedBundle\Entity\Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \Axipi\FeedBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set action
     *
     * @param \Axipi\FeedBundle\Entity\Action $action
     *
     * @return ActionCategory
     */
    public function setAction(\Axipi\FeedBundle\Entity\Action $action = null)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return \Axipi\FeedBundle\Entity\Action
     */
    public function getAction()
    {
        return $this->action;
    }
}

