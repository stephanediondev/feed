<?php

namespace App\Tests\Entity;

use App\Entity\Action;
use App\Entity\ActionCategory;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CategoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    public function testToString()
    {
        $this->assertTrue(method_exists(new Category(), '__toString'), 'method __toString missing');
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

        //add action category (cascade persist)
        $actionCategory = new ActionCategory();
        $actionCategory->setAction($action);
        $category->addAction($actionCategory);
        $this->assertTrue($category->hasAction($actionCategory));

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        //remove (orphan removal)
        $category->removeAction($actionCategory);
        $this->assertFalse($category->hasAction($actionCategory));

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        //remove
        $this->entityManager->remove($category);
        $this->entityManager->remove($action);
        $this->entityManager->flush();
    }
}
