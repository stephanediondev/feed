<?php

namespace Readerself\CoreBundle\Entity;

/**
 * FeedCategory
 */
class FeedCategory
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Readerself\CoreBundle\Entity\Category
     */
    private $category;

    /**
     * @var \Readerself\CoreBundle\Entity\Feed
     */
    private $feed;


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
     * Set category
     *
     * @param \Readerself\CoreBundle\Entity\Category $category
     *
     * @return FeedCategory
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
     * Set feed
     *
     * @param \Readerself\CoreBundle\Entity\Feed $feed
     *
     * @return FeedCategory
     */
    public function setFeed(\Readerself\CoreBundle\Entity\Feed $feed = null)
    {
        $this->feed = $feed;

        return $this;
    }

    /**
     * Get feed
     *
     * @return \Readerself\CoreBundle\Entity\Feed
     */
    public function getFeed()
    {
        return $this->feed;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->getCategory()->getId(),
            'title' => $this->getCategory()->getTitle(),
        ];
    }
}
