<?php

namespace App\Tests\Entity;

use App\Entity\Action;
use App\Entity\ActionItem;
use App\Entity\Author;
use App\Entity\Category;
use App\Entity\Enclosure;
use App\Entity\Feed;
use App\Entity\Item;
use App\Entity\ItemCategory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ItemTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    public function testToString()
    {
        $this->assertTrue(method_exists(new Item(), '__toString'), 'method __toString missing');
    }

    public function testPersist()
    {
        $value = 'test-'.uniqid();

        //add author
        $author = new Author();
        $author->setTitle($value);

        $this->entityManager->persist($author);
        $this->entityManager->flush();

        //add feed
        $feed = new Feed();
        $feed->setTitle($value);
        $feed->setLink($value);

        $this->entityManager->persist($feed);
        $this->entityManager->flush();

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

        //add item
        $item = new Item();
        $item->setTitle($value);
        $item->setLink($value);
        $item->setFeed($feed);
        $item->setAuthor($author);
        $item->setDate(new \Datetime());

        //add action item (cascade persist)
        $actionItem = new ActionItem();
        $actionItem->setAction($action);
        $item->addAction($actionItem);
        $this->assertTrue($item->hasAction($actionItem));

        //add item category (cascade persist)
        $itemCategory = new ItemCategory();
        $itemCategory->setCategory($category);
        $item->addCategory($itemCategory);
        $this->assertTrue($item->hasCategory($itemCategory));

        //add enclosure (cascade persist)
        $enclosure = new Enclosure();
        $enclosure->setLink($value);
        $enclosure->setType($value);
        $item->addEnclosure($enclosure);
        $this->assertTrue($item->hasCategory($itemCategory));

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        //remove (orphan removal)
        $item->removeAction($actionItem);
        $this->assertFalse($item->hasAction($actionItem));

        $item->removeCategory($itemCategory);
        $this->assertFalse($item->hasCategory($itemCategory));

        $item->removeEnclosure($enclosure);
        $this->assertFalse($item->hasEnclosure($enclosure));

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        //remove
        $this->entityManager->remove($item);
        $this->entityManager->remove($action);
        $this->entityManager->remove($category);
        $this->entityManager->remove($feed);
        $this->entityManager->remove($author);
        $this->entityManager->flush();
    }
}
