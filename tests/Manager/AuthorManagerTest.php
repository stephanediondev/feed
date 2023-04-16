<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Entity\Author;
use App\Manager\AuthorManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AuthorManagerTest extends KernelTestCase
{
    protected AuthorManager $authorManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->authorManager = static::getContainer()->get('App\Manager\AuthorManager');
    }

    public function testPersist(): void
    {
        $author = new Author();
        $author->setTitle('test-'.uniqid('', true));

        $this->authorManager->persist($author);

        $this->assertIsInt($author->getId());

        $this->authorManager->remove($author);
    }

    public function testGetOne(): void
    {
        $test = $this->authorManager->getOne(['id' => 0]);
        $this->assertNull($test);
    }
}
