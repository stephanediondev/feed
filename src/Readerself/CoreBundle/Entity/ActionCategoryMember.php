<?php

namespace Readerself\CoreBundle\Entity;

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
     * @var \Readerself\CoreBundle\Entity\Category
     */
    private $category;

    /**
     * @var \Readerself\CoreBundle\Entity\Member
     */
    private $member;

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
     * @param \Readerself\CoreBundle\Entity\Category $category
     *
     * @return ActionCategoryMember
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
     * Set member
     *
     * @param \Readerself\CoreBundle\Entity\Member $member
     *
     * @return ActionCategoryMember
     */
    public function setMember(\Readerself\CoreBundle\Entity\Member $member = null)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get member
     *
     * @return \Readerself\CoreBundle\Entity\Member
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Set action
     *
     * @param \Readerself\CoreBundle\Entity\Action $action
     *
     * @return ActionCategoryMember
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

