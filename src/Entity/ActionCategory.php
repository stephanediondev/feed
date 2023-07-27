<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\ActionTrait;
use App\Repository\ActionCategoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActionCategoryRepository::class)]
#[ORM\Table(name: "action_category")]
#[ORM\Index(name: "action_id", columns: ["action_id"])]
#[ORM\Index(name: "category_id", columns: ["category_id"])]
#[ORM\Index(name: "member_id", columns: ["member_id"])]
class ActionCategory
{
    use ActionTrait;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Category", inversedBy: "actions", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "category_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
    private ?Category $category = null;

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }
}
