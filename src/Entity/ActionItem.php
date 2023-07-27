<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\ActionTrait;
use App\Repository\ActionItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActionItemRepository::class)]
#[ORM\Table(name: "action_item")]
#[ORM\Index(name: "action_id", columns: ["action_id"])]
#[ORM\Index(name: "item_id", columns: ["item_id"])]
#[ORM\Index(name: "member_id", columns: ["member_id"])]
class ActionItem
{
    use ActionTrait;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Item", inversedBy: "actions", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "item_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
    private ?Item $item = null;

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): self
    {
        $this->item = $item;

        return $this;
    }
}
