<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ItemCategoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemCategoryRepository::class)]
#[ORM\Table(name: "item_category")]
#[ORM\UniqueConstraint(name: "item_id_category_id", columns: ["item_id", "category_id"])]
#[ORM\Index(name: "item_id", columns: ["item_id"])]
#[ORM\Index(name: "category_id", columns: ["category_id"])]
class ItemCategory
{
    #[ORM\Column(name: "id", type: "integer", options: ["unsigned" => true]), ORM\Id, ORM\GeneratedValue(strategy: "IDENTITY")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Category", inversedBy: "", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "category_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
    private ?Category $category = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Item", inversedBy: "categories", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "item_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
    private ?Item $item = null;

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

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): self
    {
        $this->item = $item;

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
