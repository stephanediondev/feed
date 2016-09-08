<?php

namespace Readerself\CoreBundle\Entity;

/**
 * Item
 */
class Item
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
    private $link;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     */
    private $contentFull;

    /**
     * @var float
     */
    private $latitude;

    /**
     * @var float
     */
    private $longitude;

    /**
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * @var \Readerself\CoreBundle\Entity\Feed
     */
    private $feed;

    /**
     * @var \Readerself\CoreBundle\Entity\Author
     */
    private $author;

    /**
     * @var ArrayCollection
     */
    private $enclosures;


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
     * @return Item
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
     * Set link
     *
     * @param string $link
     *
     * @return Item
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Item
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Item
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set contentFull
     *
     * @param string $contentFull
     *
     * @return Item
     */
    public function setContentFull($contentFull)
    {
        $this->contentFull = $contentFull;

        return $this;
    }

    /**
     * Get contentFull
     *
     * @return string
     */
    public function getContentFull()
    {
        return $this->contentFull;
    }

    /**
     * Set latitude
     *
     * @param float $latitude
     *
     * @return Item
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param float $longitude
     *
     * @return Item
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     *
     * @return Item
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
     * Set feed
     *
     * @param \Readerself\CoreBundle\Entity\Feed $feed
     *
     * @return Item
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
     * Set author
     *
     * @param \Readerself\CoreBundle\Entity\Author $author
     *
     * @return Item
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
     * @return ArrayCollection
     */
    public function getEnclosures()
    {
        return $this->enclosures;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        if($this->getAuthor()) {
            $author = $this->getAuthor()->toArray();
        } else {
            $author = false;
        }

        return [
            'id' => $this->getId(),
            'feed' => $this->getFeed()->toArray(),
            'author' => $author,
            'title' => $this->getTitle(),
            'link' => $this->getLink(),
            'date' => $this->getDate(),
            'content' => $this->getContent(),
            'content_full' => $this->getContentFull(),
            'latitude' => $this->getLatitude(),
            'longitude' => $this->getLongitude(),
        ];
    }
}
