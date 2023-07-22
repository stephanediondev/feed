<?php

namespace App\Tests\Entity;

use App\Entity\Action;
use App\Entity\ActionFeed;
use App\Entity\Category;
use App\Entity\Feed;
use App\Entity\FeedCategory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FeedTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    public function testToString()
    {
        $this->assertTrue(method_exists(new Feed(), '__toString'), 'method __toString missing');
    }

    public function testPersist()
    {
        $value = 'test-'.uniqid();

        //add action
        $action = new Action();
        $action->setTitle($value);

        $this->entityManager->persist($action);
        $this->entityManager->flush();

        //add category
        $category = new Category();
        $category->setTitle($value);

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        //add feed
        $feed = new Feed();
        $feed->setTitle($value);
        $feed->setLink($value);

        //add action feed (cascade persist)
        $actionFeed = new ActionFeed();
        $actionFeed->setAction($action);
        $feed->addAction($actionFeed);
        $this->assertTrue($feed->hasAction($actionFeed));

        //add feed category (cascade persist)
        $feedCategory = new FeedCategory();
        $feedCategory->setCategory($category);
        $feed->addCategory($feedCategory);
        $this->assertTrue($feed->hasCategory($feedCategory));

        $this->entityManager->persist($feed);
        $this->entityManager->flush();

        //remove (orphan removal)
        $feed->removeAction($actionFeed);
        $this->assertFalse($feed->hasAction($actionFeed));

        $feed->removeCategory($feedCategory);
        $this->assertFalse($feed->hasCategory($feedCategory));

        $this->entityManager->persist($feed);
        $this->entityManager->flush();

        //remove
        $this->entityManager->remove($feed);
        $this->entityManager->remove($action);
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }
}
