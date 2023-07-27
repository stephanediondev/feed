<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\ActionTrait;
use App\Repository\ActionAuthorRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActionAuthorRepository::class)]
#[ORM\Table(name: "action_author")]
#[ORM\Index(name: "action_id", columns: ["action_id"])]
#[ORM\Index(name: "author_id", columns: ["author_id"])]
#[ORM\Index(name: "member_id", columns: ["member_id"])]
class ActionAuthor
{
    use ActionTrait;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Author", inversedBy: "actions", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "author_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
    private ?Author $author = null;

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(?Author $author): self
    {
        $this->author = $author;

        return $this;
    }
}
