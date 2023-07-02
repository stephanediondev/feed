<?php

namespace App\Tests\Entity;

use App\Entity\Action;
use App\Entity\Author;
use App\Entity\ActionAuthor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AuthorTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    public function testToString()
    {
        $this->assertTrue(method_exists(new Author(), '__toString'), 'method __toString missing');
    }

    public function testPersist()
    {
        $value = 'test-'.uniqid();

        //add action
        $action = new Action();
        $action->setTitle($value);

        $this->entityManager->persist($action);
        $this->entityManager->flush();

        //add author
        $author = new Author();
        $author->setTitle($value);

        //add action author (cascade persist)
        $actionAuthor = new ActionAuthor();
        $actionAuthor->setAction($action);
        $author->addAction($actionAuthor);
        $this->assertTrue($author->hasAction($actionAuthor));

        $this->entityManager->persist($author);
        $this->entityManager->flush();

        //remove (orphan removal)
        $author->removeAction($actionAuthor);
        $this->assertFalse($author->hasAction($actionAuthor));

        $this->entityManager->persist($author);
        $this->entityManager->flush();

        //remove
        $this->entityManager->remove($author);
        $this->entityManager->remove($action);
        $this->entityManager->flush();
    }
}
