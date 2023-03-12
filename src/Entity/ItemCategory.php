<?php

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
    private $category;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Item", inversedBy: "", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "item_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
    private $item;

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
     * Set category
     *
     * @param \App\Entity\Category $category
     *
     * @return ItemCategory
     */
    public function setCategory(Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \App\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set item
     *
     * @param \App\Entity\Item $item
     *
     * @return ItemCategory
     */
    public function setItem(Item $item = null)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get item
     *
     * @return \App\Entity\Item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->getCategory()->getId(),
            'title' => $this->getCategory()->getTitle(),
        ];
    }
}
