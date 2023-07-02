<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ActionAuthorRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActionAuthorRepository::class)]
#[ORM\Table(name: "action_author")]
#[ORM\Index(name: "action_id", columns: ["action_id"])]
#[ORM\Index(name: "author_id", columns: ["author_id"])]
#[ORM\Index(name: "member_id", columns: ["member_id"])]
class ActionAuthor
{
    #[ORM\Column(name: "id", type: "integer", options: ["unsigned" => true]), ORM\Id, ORM\GeneratedValue(strategy: "IDENTITY")]
    private ?int $id = null;

    #[ORM\Column(name: "date_created", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateCreated = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Author", inversedBy: "actions", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "author_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
    private ?Author $author = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Action", inversedBy: "", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "action_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
    private ?Action $action = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Member", inversedBy: "", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "member_id", referencedColumnName: "id", onDelete: "cascade", nullable: true)]
    private ?Member $member = null;

    public function __construct()
    {
        $this->dateCreated = new \Datetime();
    }

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

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(?Author $author): self
    {
        $this->author = $author;

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
