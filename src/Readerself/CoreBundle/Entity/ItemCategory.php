<?php

namespace Readerself\CoreBundle\Entity;

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
     * @var \Readerself\CoreBundle\Entity\Category
     */
    private $category;

    /**
     * @var \Readerself\CoreBundle\Entity\Item
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
     * @param \Readerself\CoreBundle\Entity\Category $category
     *
     * @return ItemCategory
     */
    public function setCategory(\Readerself\CoreBundle\Entity\Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \Readerself\CoreBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set item
     *
     * @param \Readerself\CoreBundle\Entity\Item $item
     *
     * @return ItemCategory
     */
    public function setItem(\Readerself\CoreBundle\Entity\Item $item = null)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get item
     *
     * @return \Readerself\CoreBundle\Entity\Item
     */
    public function getItem()
    {
        return $this->item;
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
