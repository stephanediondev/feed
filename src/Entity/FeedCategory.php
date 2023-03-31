<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\FeedCategoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FeedCategoryRepository::class)]
#[ORM\Table(name: "feed_category")]
#[ORM\UniqueConstraint(name: "feed_id_category_id", columns: ["feed_id", "category_id"])]
#[ORM\Index(name: "feed_id", columns: ["feed_id"])]
#[ORM\Index(name: "category_id", columns: ["category_id"])]
class FeedCategory
{
    #[ORM\Column(name: "id", type: "integer", options: ["unsigned" => true]), ORM\Id, ORM\GeneratedValue(strategy: "IDENTITY")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Category", inversedBy: "", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "category_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
    private ?Category $category = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Feed", inversedBy: "categories", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "feed_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
    private ?Feed $feed = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

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
            'id' => $this->getCategory() ? $this->getCategory()->getId() : null,
            'title' => $this->getCategory() ? $this->getCategory()->getTitle() : null,
        ];
    }
}
