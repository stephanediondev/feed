<?php

namespace App\Entity;

use App\Repository\CollectionFeedRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CollectionFeedRepository::class)]
#[ORM\Table(name: "collection_feed")]
#[ORM\UniqueConstraint(name: "collection_id_feed_id", columns: ["collection_id", "feed_id"])]
#[ORM\Index(name: "collection_id", columns: ["collection_id"])]
#[ORM\Index(name: "feed_id", columns: ["feed_id"])]
class CollectionFeed
{
    #[ORM\Column(name: "id", type: "integer", options: ["unsigned" => true]), ORM\Id, ORM\GeneratedValue(strategy: "IDENTITY")]
    private ?int $id = null;

    #[ORM\Column(name: "error", type: "text", length: 65535, nullable: true)]
    private $error;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Collection", inversedBy: "", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "collection_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
    private $collection;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Feed", inversedBy: "", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "feed_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
    private $feed;

    #[ORM\Column(name: "date_created", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateCreated = null;


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
     * Set error
     *
     * @param string $error
     *
     * @return CollectionFeed
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Get error
     *
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set dateCreated
     *
     * @param \DateTimeInterface $dateCreated
     *
     * @return CollectionFeed
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
     * Set collection
     *
     * @param \App\Entity\Collection $collection
     *
     * @return CollectionFeed
     */
    public function setCollection(Collection $collection = null)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * Get collection
     *
     * @return \App\Entity\Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Set feed
     *
     * @param \App\Entity\Feed $feed
     *
     * @return CollectionFeed
     */
    public function setFeed(Feed $feed = null)
    {
        $this->feed = $feed;

        return $this;
    }

    /**
     * Get feed
     *
     * @return \App\Entity\Feed
     */
    public function getFeed()
    {
        return $this->feed;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'error' => $this->getError(),
            'date_created' => $this->getDateCreated()->format('Y-m-d H:i:s'),
        ];
    }
}
