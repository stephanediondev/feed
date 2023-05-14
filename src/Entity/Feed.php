<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\FeedRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    private ?string $title = null;

    #[ORM\Column(name: "link", type: "string", length: 255, nullable: false)]
    private ?string $link = null;

    #[ORM\Column(name: "website", type: "string", length: 255, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(name: "hostname", type: "string", length: 255, nullable: true)]
    private ?string $hostname = null;

    #[ORM\Column(name: "description", type: "text", length: 65535, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: "language", type: "string", length: 2, nullable: true, options: ["fixed" => true])]
    private ?string $language = null;

    #[ORM\Column(name: "next_collection", type: "datetime", nullable: true)]
    private ?\DateTimeInterface $nextCollection = null;

    #[ORM\Column(name: "date_created", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateCreated = null;

    #[ORM\Column(name: "date_modified", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateModified = null;

    /**
     * @var Collection<int, FeedCategory> $categories
     */
    #[ORM\OneToMany(targetEntity: "App\Entity\FeedCategory", mappedBy: "feed", fetch: "LAZY", cascade: ["persist"], orphanRemoval: true)]
    private Collection $categories;

    /**
     * @var Collection<int, CollectionFeed> $collections
     */
    #[ORM\OneToMany(targetEntity: "App\Entity\CollectionFeed", mappedBy: "feed", fetch: "LAZY", cascade: ["persist"], orphanRemoval: true)]
    private Collection $collections;

    /**
     * @var Collection<int, ActionFeed> $actions
     */
    #[ORM\OneToMany(targetEntity: "App\Entity\ActionFeed", mappedBy: "feed", fetch: "LAZY", cascade: ["persist"], orphanRemoval: true)]
    private Collection $actions;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->collections = new ArrayCollection();
        $this->actions = new ArrayCollection();
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

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getHostname(): ?string
    {
        return $this->hostname;
    }

    public function setHostname(?string $hostname): self
    {
        $this->hostname = $hostname;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getNextCollection(): ?\DateTimeInterface
    {
        return $this->nextCollection;
    }

    public function setNextCollection(?\DateTimeInterface $nextCollection): self
    {
        $this->nextCollection = $nextCollection;

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

    /**
     * @return Collection<int, FeedCategory>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }
    public function addCategory(FeedCategory $feedCategory): self
    {
        if (false === $this->hasCategory($feedCategory)) {
            $this->categories->add($feedCategory);
            $feedCategory->setFeed($this);
        }
        return $this;
    }
    public function removeCategory(FeedCategory $feedCategory): self
    {
        if (true === $this->hasCategory($feedCategory)) {
            $this->categories->removeElement($feedCategory);
            $feedCategory->setFeed(null);
        }
        return $this;
    }
    public function hasCategory(FeedCategory $feedCategory): bool
    {
        return $this->categories->contains($feedCategory);
    }

    /**
     * @return Collection<int, CollectionFeed>
     */
    public function getCollections(): Collection
    {
        return $this->collections;
    }
    public function addCollection(CollectionFeed $collecionFeed): self
    {
        if (false === $this->hasCollection($collecionFeed)) {
            $this->collections->add($collecionFeed);
            $collecionFeed->setFeed($this);
        }
        return $this;
    }
    public function removeCollection(CollectionFeed $collecionFeed): self
    {
        if (true === $this->hasCollection($collecionFeed)) {
            $this->collections->removeElement($collecionFeed);
            $collecionFeed->setFeed(null);
        }
        return $this;
    }
    public function hasCollection(CollectionFeed $collecionFeed): bool
    {
        return $this->collections->contains($collecionFeed);
    }

    /**
     * @return Collection<int, ActionFeed>
     */
    public function getActions(): Collection
    {
        return $this->actions;
    }
    public function addAction(ActionFeed $action): self
    {
        if (false === $this->hasAction($action)) {
            $this->actions->add($action);
            $action->setFeed($this);
        }
        return $this;
    }
    public function removeAction(ActionFeed $action): self
    {
        if (true === $this->hasAction($action)) {
            $this->actions->removeElement($action);
            $action->setFeed(null);
        }
        return $this;
    }
    public function hasAction(ActionFeed $action): bool
    {
        return $this->actions->contains($action);
    }

    public function getDirection(): string
    {
        $direction = 'ltr';

        if ($this->getLanguage() == 'ar' || $this->getLanguage() == 'he') {
            $direction = 'rtl';
        }

        return $direction;
    }

    public function isLinkSecure(): bool
    {
        return $this->getLink() ? str_starts_with($this->getLink(), 'https://') : false;
    }

    public function isWebsiteSecure(): bool
    {
        return $this->getWebsite() ? str_starts_with($this->getWebsite(), 'https://') : false;
    }

    public function getIconUrl(): string
    {
        return 'https://www.google.com/s2/favicons?domain='.$this->getHostname().'&alt=feed';
    }

    /**
     * @return array<mixed>
     */
    public function getJsonApiData(): array
    {
        return [
            'id' => strval($this->getId()),
            'type' => 'feed',
            'attributes' => [
                'title' => $this->getTitle(),
                'link' => $this->getLink(),
                'website' => $this->getWebsite(),
                'hostname' => $this->getHostname(),
                'icon_url' => $this->getIconUrl(),
                'description' => $this->getDescription(),
                'language' => $this->getLanguage(),
                'direction' => $this->getDirection(),
                'date_created' => $this->getDateCreated() ? $this->getDateCreated()->format('Y-m-d H:i:s') : null,
                'date_modified' => $this->getDateModified() ? $this->getDateModified()->format('Y-m-d H:i:s') : null,
                'link_secure' => $this->isLinkSecure(),
                'website_secure' => $this->isWebsiteSecure(),
            ],
        ];
    }
}
