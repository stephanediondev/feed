<?php

namespace Readerself\CoreBundle\Entity;

/**
 * Push
 */
class Push
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var string
     */
    private $publickey;

    /**
     * @var string
     */
    private $authenticationsecret;

    /**
     * @var string
     */
    private $agent;

    /**
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * @var \DateTime
     */
    private $dateModified;

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
     * Set endpoint
     *
     * @param string $endpoint
     *
     * @return Push
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * Get endpoint
     *
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Set publickey
     *
     * @param string $publickey
     *
     * @return Push
     */
    public function setPublickey($publickey)
    {
        $this->publickey = $publickey;

        return $this;
    }

    /**
     * Get publickey
     *
     * @return string
     */
    public function getPublickey()
    {
        return $this->publickey;
    }

    /**
     * Set authenticationsecret
     *
     * @param string $authenticationsecret
     *
     * @return Push
     */
    public function setAuthenticationsecret($authenticationsecret)
    {
        $this->authenticationsecret = $authenticationsecret;

        return $this;
    }

    /**
     * Get authenticationsecret
     *
     * @return string
     */
    public function getAuthenticationsecret()
    {
        return $this->authenticationsecret;
    }

    /**
     * Set agent
     *
     * @param string $agent
     *
     * @return Push
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
     * @return Push
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
     * Set dateModified
     *
     * @param \DateTime $dateModified
     *
     * @return Push
     */
    public function setDateModified($dateModified)
    {
        $this->dateModified = $dateModified;

        return $this;
    }

    /**
     * Get dateModified
     *
     * @return \DateTime
     */
    public function getDateModified()
    {
        return $this->dateModified;
    }

    /**
     * Set member
     *
     * @param \Readerself\CoreBundle\Entity\Member $member
     *
     * @return Push
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

