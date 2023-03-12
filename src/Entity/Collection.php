<?php

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
    private $feeds = '0';

    #[ORM\Column(name: "errors", type: "integer", nullable: false, options: ["unsigned" => true, "default" => 0])]
    private $errors = '0';

    #[ORM\Column(name: "time", type: "float", nullable: false, options: ["unsigned" => true, "default" => 0])]
    private $time;

    #[ORM\Column(name: "memory", type: "integer", nullable: false, options: ["unsigned" => true, "default" => 0])]
    private $memory;

    #[ORM\Column(name: "date_created", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateCreated = null;

    #[ORM\Column(name: "date_modified", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateModified = null;


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
     * Set feeds
     *
     * @param integer $feeds
     *
     * @return Collection
     */
    public function setFeeds($feeds)
    {
        $this->feeds = $feeds;

        return $this;
    }

    /**
     * Get feeds
     *
     * @return integer
     */
    public function getFeeds()
    {
        return $this->feeds;
    }

    /**
     * Set errors
     *
     * @param integer $errors
     *
     * @return Collection
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * Get errors
     *
     * @return integer
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Set time
     *
     * @param float $time
     *
     * @return Collection
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return float
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set memory
     *
     * @param integer $memory
     *
     * @return Collection
     */
    public function setMemory($memory)
    {
        $this->memory = $memory;

        return $this;
    }

    /**
     * Get memory
     *
     * @return integer
     */
    public function getMemory()
    {
        return $this->memory;
    }

    /**
     * Set dateCreated
     *
     * @param \DateTimeInterface $dateCreated
     *
     * @return Collection
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
     * Set dateModified
     *
     * @param \DateTime $dateModified
     *
     * @return Collection
     */
    public function setDateModified($dateModified)
    {
        $this->dateModified = $dateModified;

        return $this;
    }

    /**
     * Get dateModified
     *
     * @return \DateTimeInterface
     */
    public function getDateModified()
    {
        return $this->dateModified;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'date_created' => $this->getDateCreated(),
        ];
    }
}
