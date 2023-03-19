<?php

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
    private $link;

    #[ORM\Column(name: "type", type: "string", length: 255, nullable: false)]
    private $type;

    #[ORM\Column(name: "length", type: "integer", nullable: true, options: ["unsigned" => true])]
    private $length;

    #[ORM\Column(name: "width", type: "integer", nullable: true, options: ["unsigned" => true])]
    private $width;

    #[ORM\Column(name: "height", type: "integer", nullable: true, options: ["unsigned" => true])]
    private $height;

    #[ORM\Column(name: "date_created", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateCreated = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Item", inversedBy: "enclosures", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "item_id", referencedColumnName: "id", onDelete: "cascade", nullable: true)]
    private $item;

    /**
     * @var string
     */
    private $typeGroup;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set link
     *
     * @param string $link
     *
     * @return Enclosure
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Enclosure
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set length
     *
     * @param integer $length
     *
     * @return Enclosure
     */
    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }

    /**
     * Get length
     *
     * @return integer
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Set width
     *
     * @param integer $width
     *
     * @return Enclosure
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width
     *
     * @return integer
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height
     *
     * @param integer $height
     *
     * @return Enclosure
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height
     *
     * @return integer
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set dateCreated
     *
     * @param \DateTimeInterface $dateCreated
     *
     * @return Enclosure
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * Get dateCreated
     *
     * @return \DateTimeInterface
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * Set item
     *
     * @param \App\Entity\Item $item
     *
     * @return Enclosure
     */
    public function setItem(Item $item = null)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get item
     *
     * @return \App\Entity\Item
     */
    public function getItem()
    {
        return $this->item;
    }

    public function getTypeGroup()
    {
        $this->typeGroup = '';

        if (strstr($this->getType(), '/')) {
            $parts = explode('/', $this->getType());
            $this->typeGroup = $parts[0];
        }

        return $this->typeGroup;
    }

    public function isLinkSecure()
    {
        return substr($this->getLink(), 0, 6) == 'https:';
    }

    /**
     * @return array
     */
    public function toArray()
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
