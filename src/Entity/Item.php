<?php

namespace App\Entity;

use App\Repository\ItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
#[ORM\Table(name: "item")]
#[ORM\Index(name: "feed_id", columns: ["feed_id"])]
#[ORM\Index(name: "author_id", columns: ["author_id"])]
#[ORM\Index(name: "date", columns: ["date"])]
#[ORM\UniqueConstraint(name: "link", columns: ["link"])]
class Item
{
    #[ORM\Column(name: "id", type: "integer", options: ["unsigned" => true]), ORM\Id, ORM\GeneratedValue(strategy: "IDENTITY")]
    private ?int $id = null;

    #[ORM\Column(name: "title", type: "string", length: 255, nullable: false)]
    private string $title;

    #[ORM\Column(name: "link", type: "string", length: 255, nullable: false)]
    private $link;

    #[ORM\Column(name: "date", type: "datetime", nullable: false)]
    private $date;

    #[ORM\Column(name: "content", type: "string", length: 4294967295, nullable: true)]
    private $content;

    #[ORM\Column(name: "latitude", type: "float", nullable: true)]
    private $latitude;

    #[ORM\Column(name: "longitude", type: "float", nullable: true)]
    private $longitude;

    #[ORM\Column(name: "date_created", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateCreated = null;

    #[ORM\Column(name: "date_modified", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateModified = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Feed", inversedBy: "", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "feed_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
    private $feed;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Author", inversedBy: "", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "author_id", referencedColumnName: "id", onDelete: "SET NULL", nullable: true)]
    private $author;

    #[ORM\OneToMany(targetEntity: "App\Entity\Enclosure", mappedBy: "item", fetch: "LAZY", cascade: ["persist"], orphanRemoval: true)]
    private Collection $enclosures;

    public function __construct()
    {
        $this->enclosures = new ArrayCollection();
    }

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
     * @return \DateTimeInterface
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
     * @param \DateTimeInterface $dateCreated
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
     * @return Item
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

    /**
     * Set feed
     *
     * @param \App\Entity\Feed $feed
     *
     * @return Item
     */
    public function setFeed(Feed $feed = null)
    {
        $this->feed = $feed;

        return $this;
    }

    /**
     * Get feed
     *
     * @return \App\Entity\Feed
     */
    public function getFeed()
    {
        return $this->feed;
    }

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(?Author $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getEnclosures(): Collection
    {
        return $this->enclosures;
    }
    public function addEnclosure(Enclosure $enclosure): self
    {
        if (false === $this->hasEnclosure($enclosure)) {
            $this->enclosures->add($enclosure);
            $enclosure->setItem($this);
        }
        return $this;
    }
    public function removeEnclosure(Enclosure $enclosure): self
    {
        if (true === $this->hasEnclosure($enclosure)) {
            $this->enclosures->removeElement($enclosure);
            $enclosure->setItem(null);
        }
        return $this;
    }
    public function hasEnclosure(Enclosure $enclosure): bool
    {
        return $this->enclosures->contains($enclosure);
    }

    public function isLinkSecure()
    {
        return substr($this->getLink(), 0, 6) == 'https:';
    }

    /**
     * @return array
     */
    public function toArray()
    {
        if ($this->getAuthor()) {
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
            'date' => $this->getDate()->format('Y-m-d H:i:s'),
            'content' => $this->getContent(),
            'latitude' => $this->getLatitude(),
            'longitude' => $this->getLongitude(),
            'date_created' => $this->getDateCreated()->format('Y-m-d H:i:s'),
            'link_secure' => $this->isLinkSecure(),
        ];
    }
}
