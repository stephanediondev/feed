<?php

namespace Axipi\FeedBundle\Entity;

/**
 * Subscription
 */
class Subscription
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $priority = '0';

    /**
     * @var string
     */
    private $direction;

    /**
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * @var \Axipi\FeedBundle\Entity\Folder
     */
    private $folder;

    /**
     * @var \Axipi\FeedBundle\Entity\Feed
     */
    private $feed;

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
     * Set priority
     *
     * @param boolean $priority
     *
     * @return Subscription
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     *
     * @return boolean
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set direction
     *
     * @param string $direction
     *
     * @return Subscription
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
     * @return Subscription
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
     * Set folder
     *
     * @param \Axipi\FeedBundle\Entity\Folder $folder
     *
     * @return Subscription
     */
    public function setFolder(\Axipi\FeedBundle\Entity\Folder $folder = null)
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * Get folder
     *
     * @return \Axipi\FeedBundle\Entity\Folder
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * Set feed
     *
     * @param \Axipi\FeedBundle\Entity\Feed $feed
     *
     * @return Subscription
     */
    public function setFeed(\Axipi\FeedBundle\Entity\Feed $feed = null)
    {
        $this->feed = $feed;

        return $this;
    }

    /**
     * Get feed
     *
     * @return \Axipi\FeedBundle\Entity\Feed
     */
    public function getFeed()
    {
        return $this->feed;
    }

    /**
     * Set member
     *
     * @param \Axipi\FeedBundle\Entity\Member $member
     *
     * @return Subscription
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

