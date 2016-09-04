<?php

namespace Readerself\CoreBundle\Entity;

/**
 * Collection
 */
class Collection
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $feeds = '0';

    /**
     * @var integer
     */
    private $errors = '0';

    /**
     * @var float
     */
    private $time;

    /**
     * @var integer
     */
    private $memory;

    /**
     * @var \DateTime
     */
    private $dateCreated;


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
     * Set feeds
     *
     * @param integer $feeds
     *
     * @return Collection
     */
    public function setFeeds($feeds)
    {
        $this->feeds = $feeds;

        return $this;
    }

    /**
     * Get feeds
     *
     * @return integer
     */
    public function getFeeds()
    {
        return $this->feeds;
    }

    /**
     * Set errors
     *
     * @param integer $errors
     *
     * @return Collection
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * Get errors
     *
     * @return integer
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Set time
     *
     * @param float $time
     *
     * @return Collection
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return float
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set memory
     *
     * @param integer $memory
     *
     * @return Collection
     */
    public function setMemory($memory)
    {
        $this->memory = $memory;

        return $this;
    }

    /**
     * Get memory
     *
     * @return integer
     */
    public function getMemory()
    {
        return $this->memory;
    }

    /**
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     *
     * @return Collection
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
}

