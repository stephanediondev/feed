<?php
namespace Axipi\MCoreBundle\Tests\Manager;

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

    public function test()
    {
        $author = $this->authorManager->init();
        $author->setTitle('test-unitaire-'.uniqid('', true));

        $author_id = $this->authorManager->persist($author);

        $test = $this->authorManager->getOne(['id' => $author_id]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(Author::class, $test);

        $this->authorManager->remove($author);

        $test = $this->authorManager->getOne(['id' => $author_id]);
        $this->assertNull($test);
    }
}
