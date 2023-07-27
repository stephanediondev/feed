<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\IdTrait;
use App\Entity\DateCreatedTrait;
use App\Repository\ActionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActionRepository::class)]
#[ORM\Table(name: "action")]
#[ORM\UniqueConstraint(name: "title", columns: ["title"])]
#[ORM\UniqueConstraint(name: "UNIQ_47CC8C92AA779117", columns: ["reverse"])]
class Action
{
    use IdTrait;
    use DateCreatedTrait;

    #[ORM\Column(name: "title", type: "string", length: 255, nullable: false)]
    private ?string $title = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Action", inversedBy: "", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "reverse", referencedColumnName: "id", onDelete: "SET NULL", nullable: true)]
    private ?Action $reverse = null;

    public function __construct()
    {
        $this->dateCreated = new \Datetime();
    }

    public function __toString()
    {
        return $this->getTitle() ?? '';
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

    /**
     * @return array<mixed>
     */
    public function getJsonApiData(): array
    {
        return [
            'id' => strval($this->getId()),
            'type' => 'action',
            'attributes' => [
                'title' => $this->getTitle(),
                'date_created' => $this->getDateCreated() ? $this->getDateCreated()->format('Y-m-d H:i:s') : null,
            ],
        ];
    }
}
