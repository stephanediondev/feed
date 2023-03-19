<?php

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

    public function testId()
    {
        $title = 'test-'.uniqid('', true);
        $author = $this->authorManager->init();
        $author->setTitle($title);

        $author_id = $this->authorManager->persist($author);

        $test = $this->authorManager->getOne(['id' => $author_id]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(Author::class, $test);

        $this->authorManager->remove($test);

        $test = $this->authorManager->getOne(['id' => $author_id]);
        $this->assertNull($test);
    }

    public function testTitle()
    {
        $title = 'test-'.uniqid('', true);
        $author = $this->authorManager->init();
        $author->setTitle($title);

        $author_id = $this->authorManager->persist($author);

        $test = $this->authorManager->getOne(['title' => $title]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(Author::class, $test);
        $this->assertEquals($title, $test->getTitle());

        $this->authorManager->remove($test);

        $test = $this->authorManager->getOne(['title' => $title]);
        $this->assertNull($test);
    }
}
