<?php

namespace Readerself\CoreBundle\Entity;

/**
 * ActionAuthorMember
 */
class ActionAuthorMember
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
     * @return ActionAuthorMember
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
     * @return ActionAuthorMember
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
     * Set member
     *
     * @param \Readerself\CoreBundle\Entity\Member $member
     *
     * @return ActionAuthorMember
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
     * @return ActionAuthorMember
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
