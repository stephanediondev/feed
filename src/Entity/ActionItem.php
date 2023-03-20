<?php

namespace App\Entity;

use App\Repository\ActionItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActionItemRepository::class)]
#[ORM\Table(name: "action_item")]
#[ORM\Index(name: "action_id", columns: ["action_id"])]
#[ORM\Index(name: "item_id", columns: ["item_id"])]
#[ORM\Index(name: "member_id", columns: ["member_id"])]
class ActionItem
{
    #[ORM\Column(name: "id", type: "integer", options: ["unsigned" => true]), ORM\Id, ORM\GeneratedValue(strategy: "IDENTITY")]
    private ?int $id = null;

    #[ORM\Column(name: "date_created", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateCreated = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Item", inversedBy: "", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "item_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
    private ?Item $item = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Action", inversedBy: "", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "action_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
    private ?Action $action = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Member", inversedBy: "", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "member_id", referencedColumnName: "id", onDelete: "cascade", nullable: true)]
    private ?Member $member = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): self
    {
        $this->item = $item;

        return $this;
    }

    public function getAction(): ?Action
    {
        return $this->action;
    }

    public function setAction(?Action $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(?Member $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'action' => $this->getAction()->toArray(),
        ];
    }
}
