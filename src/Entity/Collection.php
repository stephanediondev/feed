<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CollectionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CollectionRepository::class)]
#[ORM\Table(name: "collection")]
class Collection
{
    #[ORM\Column(name: "id", type: "integer", options: ["unsigned" => true]), ORM\Id, ORM\GeneratedValue(strategy: "IDENTITY")]
    private ?int $id = null;

    #[ORM\Column(name: "feeds", type: "integer", nullable: false, options: ["unsigned" => true, "default" => 0])]
    private int $feeds = 0;

    #[ORM\Column(name: "errors", type: "integer", nullable: false, options: ["unsigned" => true, "default" => 0])]
    private int $errors = 0;

    #[ORM\Column(name: "time", type: "float", nullable: false, options: ["unsigned" => true, "default" => 0])]
    private float $time = 0;

    #[ORM\Column(name: "memory", type: "integer", nullable: false, options: ["unsigned" => true, "default" => 0])]
    private int $memory = 0;

    #[ORM\Column(name: "date_created", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateCreated = null;

    #[ORM\Column(name: "date_modified", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateModified = null;

    public function __construct()
    {
        $this->dateCreated = new \Datetime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFeeds(): int
    {
        return $this->feeds;
    }

    public function setFeeds(int $feeds): self
    {
        $this->feeds = $feeds;

        return $this;
    }

    public function getErrors(): int
    {
        return $this->errors;
    }

    public function setErrors(int $errors): self
    {
        $this->errors = $errors;

        return $this;
    }

    public function getTime(): float
    {
        return $this->time;
    }

    public function setTime(float $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function getMemory(): int
    {
        return $this->memory;
    }

    public function setMemory(int $memory): self
    {
        $this->memory = $memory;

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

    public function getDateModified(): ?\DateTimeInterface
    {
        return $this->dateModified;
    }

    public function setDateModified(?\DateTimeInterface $dateModified): self
    {
        $this->dateModified = $dateModified;

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getJsonApiData(): array
    {
        return [
            'id' => strval($this->getId()),
            'type' => 'collection',
            'attributes' => [
                'date_created' => $this->getDateCreated() ? $this->getDateCreated()->format('Y-m-d H:i:s') : null,
                'date_modified' => $this->getDateModified() ? $this->getDateModified()->format('Y-m-d H:i:s') : null,
            ],
        ];
    }
}
