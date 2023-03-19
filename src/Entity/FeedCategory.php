<?php

namespace App\Entity;

use App\Repository\FeedCategoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FeedCategoryRepository::class)]
#[ORM\Table(name: "feed_category")]
#[ORM\UniqueConstraint(name: "feed_id_category_id", columns: ["feed_id", "category_id"])]
#[ORM\Index(name: "feed_id", columns: ["feed_id"])]
#[ORM\Index(name: "category_id", columns: ["category_id"])]
class FeedCategory
{
    #[ORM\Column(name: "id", type: "integer", options: ["unsigned" => true]), ORM\Id, ORM\GeneratedValue(strategy: "IDENTITY")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Category", inversedBy: "", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "category_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
    private $category;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Feed", inversedBy: "", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "feed_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
    private $feed;

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
     * Set category
     *
     * @param \App\Entity\Category $category
     *
     * @return FeedCategory
     */
    public function setCategory(Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \App\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set feed
     *
     * @param \App\Entity\Feed $feed
     *
     * @return FeedCategory
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
            'id' => $this->getCategory()->getId(),
            'title' => $this->getCategory()->getTitle(),
        ];
    }
}
