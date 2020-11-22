<?php

namespace Readerself\CoreBundle\Entity;

/**
 * ActionAuthor
 */
class ActionAuthor
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
     * @var \Readerself\CoreBundle\Entity\Author
     */
    private $author;

    /**
     * @var \Readerself\CoreBundle\Entity\Action
     */
    private $action;

    /**
     * @var \Readerself\CoreBundle\Entity\Member
     */
    private $member;

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
     * @return ActionItem
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
     * Set author
     *
     * @param \Readerself\CoreBundle\Entity\Author $author
     *
     * @return ActionAuthor
     */
    public function setAuthor(\Readerself\CoreBundle\Entity\Author $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return \Readerself\CoreBundle\Entity\Author
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set action
     *
     * @param \Readerself\CoreBundle\Entity\Action $action
     *
     * @return ActionAuthor
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

    /**
     * Set member
     *
     * @param \Readerself\CoreBundle\Entity\Member $member
     *
     * @return ActionAuthor
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
}
