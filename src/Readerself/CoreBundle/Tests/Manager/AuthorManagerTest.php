<?php
namespace Readerself\CoreBundle\Tests\Manager;

use Readerself\CoreBundle\Entity\Author;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AuthorManagerTest extends KernelTestCase
{
    protected $authorManager;

    protected function setUp()
    {
        self::bootKernel();

        $this->authorManager = static::$kernel->getContainer()->get('readerself_core_manager_author');
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
