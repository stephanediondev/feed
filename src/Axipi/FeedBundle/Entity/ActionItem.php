<?php

namespace Axipi\FeedBundle\Entity;

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
     * @var \Axipi\FeedBundle\Entity\Item
     */
    private $item;

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
     * Set item
     *
     * @param \Axipi\FeedBundle\Entity\Item $item
     *
     * @return ActionItem
     */
    public function setItem(\Axipi\FeedBundle\Entity\Item $item = null)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get item
     *
     * @return \Axipi\FeedBundle\Entity\Item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set action
     *
     * @param \Axipi\FeedBundle\Entity\Action $action
     *
     * @return ActionItem
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

