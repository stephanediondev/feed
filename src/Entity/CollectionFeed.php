<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\CollectionFeedRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CollectionFeedRepository::class)]
#[ORM\Table(name: "collection_feed")]
#[ORM\UniqueConstraint(name: "collection_id_feed_id", columns: ["collection_id", "feed_id"])]
#[ORM\Index(name: "collection_id", columns: ["collection_id"])]
#[ORM\Index(name: "feed_id", columns: ["feed_id"])]
class CollectionFeed
{
    #[ORM\Column(name: "id", type: "integer", options: ["unsigned" => true]), ORM\Id, ORM\GeneratedValue(strategy: "IDENTITY")]
    private ?int $id = null;

    #[ORM\Column(name: "error", type: "text", length: 65535, nullable: true)]
    private ?string $error = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Collection", inversedBy: "", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "collection_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
    private ?Collection $collection = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Feed", inversedBy: "collections", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "feed_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
    private ?Feed $feed = null;

    #[ORM\Column(name: "date_created", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateCreated = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(?string $error): self
    {
        $this->error = $error;

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

    public function getCollection(): ?Collection
    {
        return $this->collection;
    }

    public function setCollection(?Collection $collection): self
    {
        $this->collection = $collection;

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

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'error' => $this->getError(),
            'date_created' => $this->getDateCreated() ? $this->getDateCreated()->format('Y-m-d H:i:s') : null,
        ];
    }
}
