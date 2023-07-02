<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enclosure;
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
     * @var Collection<int, ItemCategory> $categories
     */
    #[ORM\OneToMany(targetEntity: "App\Entity\ItemCategory", mappedBy: "item", fetch: "LAZY", cascade: ["persist"], orphanRemoval: true)]
    private Collection $categories;

    /**
     * @var Collection<int, Enclosure> $enclosures
     */
    #[ORM\OneToMany(targetEntity: "App\Entity\Enclosure", mappedBy: "item", fetch: "LAZY", cascade: ["persist"], orphanRemoval: true)]
    private Collection $enclosures;

    /**
     * @var Collection<int, ActionItem> $actions
     */
    #[ORM\OneToMany(targetEntity: "App\Entity\ActionItem", mappedBy: "item", fetch: "LAZY", cascade: ["persist"], orphanRemoval: true)]
    private Collection $actions;

    public function __construct()
    {
        $this->dateCreated = new \Datetime();
        $this->dateModified = new \Datetime();
        $this->categories = new ArrayCollection();
        $this->enclosures = new ArrayCollection();
        $this->actions = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getTitle() ?? '';
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
     * @return Collection<int, ItemCategory>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }
    public function addCategory(ItemCategory $itemCategory): self
    {
        if (false === $this->hasCategory($itemCategory)) {
            $this->categories->add($itemCategory);
            $itemCategory->setItem($this);
        }
        return $this;
    }
    public function removeCategory(ItemCategory $itemCategory): self
    {
        if (true === $this->hasCategory($itemCategory)) {
            $this->categories->removeElement($itemCategory);
            $itemCategory->setItem(null);
        }
        return $this;
    }
    public function hasCategory(ItemCategory $itemCategory): bool
    {
        return $this->categories->contains($itemCategory);
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

    /**
     * @return Collection<int, ActionItem>
     */
    public function getActions(): Collection
    {
        return $this->actions;
    }
    public function addAction(ActionItem $action): self
    {
        if (false === $this->hasAction($action)) {
            $this->actions->add($action);
            $action->setItem($this);
        }
        return $this;
    }
    public function removeAction(ActionItem $action): self
    {
        if (true === $this->hasAction($action)) {
            $this->actions->removeElement($action);
            $action->setItem(null);
        }
        return $this;
    }
    public function hasAction(ActionItem $action): bool
    {
        return $this->actions->contains($action);
    }

    public function isLinkSecure(): bool
    {
        return $this->getLink() ? str_starts_with($this->getLink(), 'https://') : false;
    }

    /**
     * @return array<mixed>
     */
    public function getJsonApiData(): array
    {
        $relationships = [];

        if ($this->getFeed()) {
            $relationships['feed'] = [
                'data' => [
                    'id' => strval($this->getFeed()->getId()),
                    'type' => 'feed',
                ],
            ];
        }

        if ($this->getAuthor()) {
            $relationships['author'] = [
                'data' => [
                    'id' => strval($this->getAuthor()->getId()),
                    'type' => 'author',
                ],
            ];
        }

        return [
            'id' => strval($this->getId()),
            'type' => 'item',
            'attributes' => [
                'title' => $this->getTitle(),
                'link' => $this->getLink(),
                'date' => $this->getDate() ? $this->getDate()->format('Y-m-d H:i:s') : null,
                'content' => $this->getContent(),
                'latitude' => $this->getLatitude(),
                'longitude' => $this->getLongitude(),
                'date_created' => $this->getDateCreated() ? $this->getDateCreated()->format('Y-m-d H:i:s') : null,
                'date_modified' => $this->getDateModified() ? $this->getDateModified()->format('Y-m-d H:i:s') : null,
                'link_secure' => $this->isLinkSecure(),
            ],
            'relationships' => $relationships,
        ];
    }

    /**
     * @return array<mixed>
     */
    public function getJsonApiIncluded(): array
    {
        $included = [];

        if ($this->getFeed()) {
            $included['feed-'.$this->getFeed()->getId()] = $this->getFeed()->getJsonApiData();
        }

        if ($this->getAuthor()) {
            $included['author-'.$this->getAuthor()->getId()] = $this->getAuthor()->getJsonApiData();
        }

        return $included;
    }
}
