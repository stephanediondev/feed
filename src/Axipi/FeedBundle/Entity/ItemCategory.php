<?php

namespace Axipi\FeedBundle\Entity;

/**
 * ItemCategory
 */
class ItemCategory
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Axipi\FeedBundle\Entity\Category
     */
    private $category;

    /**
     * @var \Axipi\FeedBundle\Entity\Item
     */
    private $item;


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
     * @param \Axipi\FeedBundle\Entity\Category $category
     *
     * @return ItemCategory
     */
    public function setCategory(\Axipi\FeedBundle\Entity\Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \Axipi\FeedBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set item
     *
     * @param \Axipi\FeedBundle\Entity\Item $item
     *
     * @return ItemCategory
     */
    public function setItem(\Axipi\FeedBundle\Entity\Item $item = null)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get item
     *
     * @return \Axipi\FeedBundle\Entity\Item
     */
    public function getItem()
    {
        return $this->item;
    }
}

