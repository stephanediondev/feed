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

    private string $direction;

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

    public function getDescription()
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

    public function getDirection(): string
    {
        $this->direction = 'ltr';

        if ($this->getLanguage() == 'ar' || $this->getLanguage() == 'he') {
            $this->direction = 'rtl';
        }

        return $this->direction;
    }

    public function isLinkSecure(): bool
    {
        return substr($this->getLink(), 0, 6) == 'https:';
    }

    public function isWebsiteSecure(): bool
    {
        return substr($this->getWebsite(), 0, 6) == 'https:';
    }

    public function toArray(): array
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
