<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Entity\Category;
use App\Entity\Item;
use App\Entity\Feed;
use App\Entity\ItemCategory;
use App\Manager\ItemManager;
use App\Manager\CategoryManager;
use App\Manager\FeedManager;
use App\Manager\ItemCategoryManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ItemCategoryManagerTest extends KernelTestCase
{
    protected CategoryManager $categoryManager;

    protected FeedManager $feedManager;

    protected ItemCategoryManager $itemCategoryManager;

    protected ItemManager $itemManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->categoryManager = static::getContainer()->get('App\Manager\CategoryManager');

        $this->feedManager = static::getContainer()->get('App\Manager\FeedManager');

        $this->itemCategoryManager = static::getContainer()->get('App\Manager\ItemCategoryManager');

        $this->itemManager = static::getContainer()->get('App\Manager\ItemManager');
    }

    public function testPersist(): void
    {
        $category = new Category();
        $category->setTitle('test-'.uniqid('', true));

        $this->categoryManager->persist($category);

        $feed = new Feed();
        $feed->setTitle('test-'.uniqid('', true));
        $feed->setLink('test-'.uniqid('', true));

        $this->feedManager->persist($feed);

        $item = new Item();
        $item->setFeed($feed);
        $item->setTitle('test-'.uniqid('', true));
        $item->setLink('test-'.uniqid('', true));
        $item->setDate(new \Datetime());

        $this->itemManager->persist($item);

        $itemCategory = new ItemCategory();
        $itemCategory->setCategory($category);
        $itemCategory->setItem($item);

        $this->itemCategoryManager->persist($itemCategory);

        $this->assertIsInt($itemCategory->getId());

        $this->itemCategoryManager->remove($itemCategory);

        $this->itemManager->remove($item);

        $this->feedManager->remove($feed);

        $this->categoryManager->remove($category);
    }

    public function testGetOne(): void
    {
        $test = $this->itemCategoryManager->getOne(['id' => 0]);
        $this->assertNull($test);
    }
}
