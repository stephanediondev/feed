<?php

namespace App\Entity;

use App\Repository\FeedRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FeedRepository::class)]
#[ORM\Table(name: "feed")]
#[ORM\Index(name: "next_collection", columns: ["next_collection"])]
#[ORM\UniqueConstraint(name: "link", columns: ["link"])]
class Feed
{
    #[ORM\Column(name: "id", type: "integer", options: ["unsigned" => true]), ORM\Id, ORM\GeneratedValue(strategy: "IDENTITY")]
    private ?int $id = null;

    #[ORM\Column(name: "title", type: "string", length: 255, nullable: true)]
    private string $title;

    #[ORM\Column(name: "link", type: "string", length: 255, nullable: false)]
    private $link;

    #[ORM\Column(name: "website", type: "string", length: 255, nullable: true)]
    private $website;

    #[ORM\Column(name: "hostname", type: "string", length: 255, nullable: true)]
    private $hostname;

    #[ORM\Column(name: "description", type: "text", length: 65535, nullable: true)]
    private $description;

    #[ORM\Column(name: "language", type: "string", length: 2, nullable: true, options: ["fixed" => true])]
    private $language;

    #[ORM\Column(name: "next_collection", type: "datetime", nullable: true)]
    private $nextCollection;

    #[ORM\Column(name: "date_created", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateCreated = null;

    #[ORM\Column(name: "date_modified", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateModified = null;

    /**
     * @var string
     */
    private $direction;


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
     * @return Feed
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
     * @return Feed
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
     * Set website
     *
     * @param string $website
     *
     * @return Feed
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set hostname
     *
     * @param string $hostname
     *
     * @return Feed
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;

        return $this;
    }

    /**
     * Get hostname
     *
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Feed
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set language
     *
     * @param string $language
     *
     * @return Feed
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set nextCollection
     *
     * @param \DateTime $nextCollection
     *
     * @return Feed
     */
    public function setNextCollection($nextCollection)
    {
        $this->nextCollection = $nextCollection;

        return $this;
    }

    /**
     * Get nextCollection
     *
     * @return \DateTimeInterface
     */
    public function getNextCollection()
    {
        return $this->nextCollection;
    }

    /**
     * Set dateCreated
     *
     * @param \DateTimeInterface $dateCreated
     *
     * @return Feed
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * Get dateCreated
     *
     * @return \DateTimeInterface
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
     * @return Feed
     */
    public function setDateModified($dateModified)
    {
        $this->dateModified = $dateModified;

        return $this;
    }

    /**
     * Get dateModified
     *
     * @return \DateTimeInterface
     */
    public function getDateModified()
    {
        return $this->dateModified;
    }

    public function getDirection()
    {
        $this->direction = 'ltr';

        if ($this->getLanguage() == 'ar' || $this->getLanguage() == 'he') {
            $this->direction = 'rtl';
        }

        return $this->direction;
    }

    public function isLinkSecure()
    {
        return substr($this->getLink(), 0, 6) == 'https:';
    }

    public function isWebsiteSecure()
    {
        return substr($this->getWebsite(), 0, 6) == 'https:';
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'link' => $this->getLink(),
            'website' => $this->getWebsite(),
            'hostname' => $this->getHostname(),
            'description' => $this->getDescription(),
            'language' => $this->getLanguage(),
            'direction' => $this->getDirection(),
            'date_created' => $this->getDateCreated()->format('Y-m-d H:i:s'),
            'link_secure' => $this->isLinkSecure(),
            'website_secure' => $this->isWebsiteSecure(),
        ];
    }
}
