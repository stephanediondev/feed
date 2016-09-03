<?php

namespace Axipi\FeedBundle\Entity;

/**
 * ActionItemMember
 */
class ActionItemMember
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * @var \Axipi\FeedBundle\Entity\Item
     */
    private $item;

    /**
     * @var \Axipi\FeedBundle\Entity\Member
     */
    private $member;

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
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     *
     * @return ActionItemMember
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * Get dateCreated
     *
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * Set item
     *
     * @param \Axipi\FeedBundle\Entity\Item $item
     *
     * @return ActionItemMember
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
     * Set member
     *
     * @param \Axipi\FeedBundle\Entity\Member $member
     *
     * @return ActionItemMember
     */
    public function setMember(\Axipi\FeedBundle\Entity\Member $member = null)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get member
     *
     * @return \Axipi\FeedBundle\Entity\Member
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Set action
     *
     * @param \Axipi\FeedBundle\Entity\Action $action
     *
     * @return ActionItemMember
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

