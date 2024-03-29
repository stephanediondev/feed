<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Entity\Category;
use App\Entity\Feed;
use App\Entity\FeedCategory;
use App\Manager\CategoryManager;
use App\Manager\FeedCategoryManager;
use App\Manager\FeedManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FeedCategoryManagerTest extends KernelTestCase
{
    protected CategoryManager $categoryManager;

    protected FeedManager $feedManager;

    protected FeedCategoryManager $feedCategoryManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->categoryManager = static::getContainer()->get('App\Manager\CategoryManager');

        $this->feedManager = static::getContainer()->get('App\Manager\FeedManager');

        $this->feedCategoryManager = static::getContainer()->get('App\Manager\FeedCategoryManager');
    }

    public function testPersist(): void
    {
        $category = new Category();
        $category->setTitle(uniqid('phpunit-'));

        $this->categoryManager->persist($category);

        $feed = new Feed();
        $feed->setTitle(uniqid('phpunit-'));
        $feed->setLink(uniqid('phpunit-'));

        $this->feedManager->persist($feed);

        $feedCategory = new FeedCategory();
        $feedCategory->setCategory($category);
        $feedCategory->setFeed($feed);

        $this->feedCategoryManager->persist($feedCategory);

        $this->assertIsInt($feedCategory->getId());

        $this->feedCategoryManager->remove($feedCategory);

        $this->feedManager->remove($feed);

        $this->categoryManager->remove($category);
    }

    public function testGetOne(): void
    {
        $test = $this->feedCategoryManager->getOne(['id' => 0]);
        $this->assertNull($test);
    }

    public function testGetList(): void
    {
        $test = $this->feedCategoryManager->getList(['id' => 0])->getResult();
        $this->assertIsArray($test);
        $this->assertCount(0, $test);
    }
}
