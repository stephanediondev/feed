<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\EnclosureRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EnclosureRepository::class)]
#[ORM\Table(name: "enclosure")]
#[ORM\Index(name: "type", columns: ["type"])]
#[ORM\Index(name: "item_id", columns: ["item_id"])]
class Enclosure
{
    #[ORM\Column(name: "id", type: "integer", options: ["unsigned" => true]), ORM\Id, ORM\GeneratedValue(strategy: "IDENTITY")]
    private ?int $id = null;

    #[ORM\Column(name: "link", type: "string", length: 255, nullable: false)]
    private ?string $link = null;

    #[ORM\Column(name: "type", type: "string", length: 255, nullable: false)]
    private ?string $type = null;

    #[ORM\Column(name: "length", type: "integer", nullable: true, options: ["unsigned" => true])]
    private ?int $length = null;

    #[ORM\Column(name: "width", type: "integer", nullable: true, options: ["unsigned" => true])]
    private ?int $width = null;

    #[ORM\Column(name: "height", type: "integer", nullable: true, options: ["unsigned" => true])]
    private ?int $height = null;

    #[ORM\Column(name: "date_created", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateCreated = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Item", inversedBy: "enclosures", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "item_id", referencedColumnName: "id", onDelete: "cascade", nullable: true)]
    private ?Item $item = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(?int $length): self
    {
        $this->length = $length;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): self
    {
        $this->height = $height;

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

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): self
    {
        $this->item = $item;

        return $this;
    }

    public function getTypeGroup(): ?string
    {
        if ($this->getType() && strstr($this->getType(), '/')) {
            $parts = explode('/', $this->getType());
            return $parts[0];
        }

        return null;
    }

    public function isLinkSecure(): bool
    {
        return substr($this->getLink(), 0, 6) == 'https:';
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'link' => $this->getLink(),
            'type' => $this->getType(),
            'type_group' => $this->getTypeGroup(),
            'link_secure' => $this->isLinkSecure(),
        ];
    }
}
