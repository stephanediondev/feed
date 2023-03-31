<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ActionCategoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActionCategoryRepository::class)]
#[ORM\Table(name: "action_category")]
#[ORM\Index(name: "action_id", columns: ["action_id"])]
#[ORM\Index(name: "category_id", columns: ["category_id"])]
#[ORM\Index(name: "member_id", columns: ["member_id"])]
class ActionCategory
{
    #[ORM\Column(name: "id", type: "integer", options: ["unsigned" => true]), ORM\Id, ORM\GeneratedValue(strategy: "IDENTITY")]
    private ?int $id = null;

    #[ORM\Column(name: "date_created", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateCreated = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Category", inversedBy: "actions", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "category_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
    private ?Category $category = null;

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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

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

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return [
            'action' => $this->getAction() ? $this->getAction()->toArray() : null,
        ];
    }
}
