<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\ActionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActionRepository::class)]
#[ORM\Table(name: "action")]
#[ORM\UniqueConstraint(name: "title", columns: ["title"])]
#[ORM\UniqueConstraint(name: "UNIQ_47CC8C92AA779117", columns: ["reverse"])]
class Action
{
    #[ORM\Column(name: "id", type: "integer", options: ["unsigned" => true]), ORM\Id, ORM\GeneratedValue(strategy: "IDENTITY")]
    private ?int $id = null;

    #[ORM\Column(name: "title", type: "string", length: 255, nullable: false)]
    private ?string $title = null;

    #[ORM\Column(name: "date_created", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateCreated = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Action", inversedBy: "", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "reverse", referencedColumnName: "id", onDelete: "SET NULL", nullable: true)]
    private ?Action $reverse = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

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

    public function getReverse(): ?Action
    {
        return $this->reverse;
    }

    public function setReverse(?Action $reverse): self
    {
        $this->reverse = $reverse;

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
        ];
    }
}
