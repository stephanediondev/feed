<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\DateCreatedTrait;
use App\Entity\IdTrait;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: "category")]
#[ORM\UniqueConstraint(name: "title", columns: ["title"])]
class Category
{
    use IdTrait;
    use DateCreatedTrait;

    #[ORM\Column(name: "title", type: "string", length: 255, nullable: false)]
    private ?string $title = null;

    /**
     * @var Collection<int, ActionCategory> $actions
     */
    #[ORM\OneToMany(targetEntity: "App\Entity\ActionCategory", mappedBy: "category", fetch: "LAZY", cascade: ["persist"], orphanRemoval: true)]
    private Collection $actions;

    public function __construct()
    {
        $this->dateCreated = new \Datetime();
        $this->actions = new ArrayCollection();
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

    /**
     * @return Collection<int, ActionCategory>
     */
    public function getActions(): Collection
    {
        return $this->actions;
    }
    public function addAction(ActionCategory $action): self
    {
        if (false === $this->hasAction($action)) {
            $this->actions->add($action);
            $action->setCategory($this);
        }
        return $this;
    }
    public function removeAction(ActionCategory $action): self
    {
        if (true === $this->hasAction($action)) {
            $this->actions->removeElement($action);
            $action->setCategory(null);
        }
        return $this;
    }
    public function hasAction(ActionCategory $action): bool
    {
        return $this->actions->contains($action);
    }

    /**
     * @return array<mixed>
     */
    public function getJsonApiData(): array
    {
        return [
            'id' => strval($this->getId()),
            'type' => 'category',
            'attributes' => [
                'title' => $this->getTitle(),
                'date_created' => $this->getDateCreated() ? $this->getDateCreated()->format('Y-m-d H:i:s') : null,
            ],
        ];
    }
}
