<?php

namespace App\Tests\Entity;

use App\Entity\Action;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ActionTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    public function testToString()
    {
        $this->assertTrue(method_exists(new Action(), '__toString'), 'method __toString missing');
    }

    public function testPersist()
    {
        $value = 'test-'.uniqid();

        //add action
        $action = new Action();
        $action->setTitle($value);

        $this->entityManager->persist($action);
        $this->entityManager->flush();

        //remove
        $this->entityManager->remove($action);
        $this->entityManager->flush();

        $this->assertTrue(true);
    }
}
