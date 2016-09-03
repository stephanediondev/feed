<?php

namespace Axipi\FeedBundle\Entity;

/**
 * Folder
 */
class Folder
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $direction;

    /**
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * @var \Axipi\FeedBundle\Entity\Member
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
     * Set title
     *
     * @param string $title
     *
     * @return Folder
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set direction
     *
     * @param string $direction
     *
     * @return Folder
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;

        return $this;
    }

    /**
     * Get direction
     *
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     *
     * @return Folder
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
     * Set member
     *
     * @param \Axipi\FeedBundle\Entity\Member $member
     *
     * @return Folder
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
}

