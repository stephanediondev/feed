<?php

namespace Axipi\FeedBundle\Entity;

/**
 * CollectionFeed
 */
class CollectionFeed
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $error;

    /**
     * @var \Axipi\FeedBundle\Entity\Collection
     */
    private $collection;

    /**
     * @var \Axipi\FeedBundle\Entity\Feed
     */
    private $feed;

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
     * Set error
     *
     * @param string $error
     *
     * @return CollectionFeed
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Get error
     *
     * @return string
     */
    public function getError()
    {
        return $this->error;
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

    /**
     * Set collection
     *
     * @param \Axipi\FeedBundle\Entity\Collection $collection
     *
     * @return CollectionFeed
     */
    public function setCollection(\Axipi\FeedBundle\Entity\Collection $collection = null)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * Get collection
     *
     * @return \Axipi\FeedBundle\Entity\Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Set feed
     *
     * @param \Axipi\FeedBundle\Entity\Feed $feed
     *
     * @return CollectionFeed
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
}

