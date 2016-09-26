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
    private $publicKey;

    /**
     * @var string
     */
    private $authenticationSecret;

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
     * Set publicKey
     *
     * @param string $publicKey
     *
     * @return Push
     */
    public function setPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;

        return $this;
    }

    /**
     * Get publicKey
     *
     * @return string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * Set authenticationSecret
     *
     * @param string $authenticationSecret
     *
     * @return Push
     */
    public function setAuthenticationSecret($authenticationSecret)
    {
        $this->authenticationSecret = $authenticationSecret;

        return $this;
    }

    /**
     * Get authenticationSecret
     *
     * @return string
     */
    public function getAuthenticationSecret()
    {
        return $this->authenticationSecret;
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

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'member' => $this->getMember()->toArray(),
            'endpoint' => $this->getEndpoint(),
            'agent' => $this->getAgent(),
            'ip' => $this->getIp(),
            'date_created' => $this->getDateCreated() ? $this->getDateCreated()->format('Y-m-d H:i:s') : '',
            'date_modified' => $this->getDateModified() ? $this->getDateModified()->format('Y-m-d H:i:s') : '',
        ];
    }
}
