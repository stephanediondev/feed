<?php

namespace App\Entity;

use App\Repository\ItemRepository;
use App\Entity\Enclosure;
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
    private ?string $title = null;

    #[ORM\Column(name: "link", type: "string", length: 255, nullable: false)]
    private ?string $link = null;

    #[ORM\Column(name: "date", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(name: "content", type: "string", length: 4294967295, nullable: true)]
    private ?string $content = null;

    #[ORM\Column(name: "latitude", type: "float", nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(name: "longitude", type: "float", nullable: true)]
    private ?float $longitude = null;

    #[ORM\Column(name: "date_created", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateCreated = null;

    #[ORM\Column(name: "date_modified", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateModified = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Feed", inversedBy: "", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "feed_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
    private ?Feed $feed = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Author", inversedBy: "", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "author_id", referencedColumnName: "id", onDelete: "SET NULL", nullable: true)]
    private ?Author $author = null;

    /**
     * @var Collection<int, Enclosure> $enclosures
     */
    #[ORM\OneToMany(targetEntity: "App\Entity\Enclosure", mappedBy: "item", fetch: "LAZY", cascade: ["persist"], orphanRemoval: true)]
    private Collection $enclosures;

    public function __construct()
    {
        $this->enclosures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(?\DateTimeInterface $dateCreated): self
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getDateModified(): ?\DateTimeInterface
    {
        return $this->dateModified;
    }

    public function setDateModified(?\DateTimeInterface $dateModified): self
    {
        $this->dateModified = $dateModified;

        return $this;
    }

    public function getFeed(): ?Feed
    {
        return $this->feed;
    }

    public function setFeed(?Feed $feed): self
    {
        $this->feed = $feed;

        return $this;
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

    /**
     * @return Collection<int, Enclosure>
     */
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

    public function isLinkSecure(): bool
    {
        return substr($this->getLink(), 0, 6) == 'https:';
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        if ($this->getAuthor()) {
            $author = $this->getAuthor()->toArray();
        } else {
            $author = null;
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
            'date_created' => $this->getDateCreated() ? $this->getDateCreated()->format('Y-m-d H:i:s') : null,
            'link_secure' => $this->isLinkSecure(),
        ];
    }
}
