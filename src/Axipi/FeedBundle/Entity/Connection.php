<?php

namespace Axipi\FeedBundle\Entity;

/**
 * Connection
 */
class Connection
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $ip;

    /**
     * @var string
     */
    private $agent;

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
     * Set token
     *
     * @param string $token
     *
     * @return Connection
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set ip
     *
     * @param string $ip
     *
     * @return Connection
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set agent
     *
     * @param string $agent
     *
     * @return Connection
     */
    public function setAgent($agent)
    {
        $this->agent = $agent;

        return $this;
    }

    /**
     * Get agent
     *
     * @return string
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     *
     * @return Connection
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
     * @return Connection
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

