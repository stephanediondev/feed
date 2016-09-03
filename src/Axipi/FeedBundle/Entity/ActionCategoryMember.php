<?php

namespace Axipi\FeedBundle\Entity;

/**
 * ActionCategoryMember
 */
class ActionCategoryMember
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
     * @var \Axipi\FeedBundle\Entity\Category
     */
    private $category;

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
     * @return ActionCategoryMember
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
     * Set category
     *
     * @param \Axipi\FeedBundle\Entity\Category $category
     *
     * @return ActionCategoryMember
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
     * Set member
     *
     * @param \Axipi\FeedBundle\Entity\Member $member
     *
     * @return ActionCategoryMember
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
     * @return ActionCategoryMember
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

