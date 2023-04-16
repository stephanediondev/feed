<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Entity\Action;
use App\Entity\ActionAuthor;
use App\Entity\Author;
use App\Manager\ActionAuthorManager;
use App\Manager\ActionManager;
use App\Manager\AuthorManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ActionAuthorManagerTest extends KernelTestCase
{
    protected ActionManager $actionManager;

    protected AuthorManager $authorManager;

    protected ActionAuthorManager $actionAuthorManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->actionManager = static::getContainer()->get('App\Manager\ActionManager');

        $this->authorManager = static::getContainer()->get('App\Manager\AuthorManager');

        $this->actionAuthorManager = static::getContainer()->get('App\Manager\ActionAuthorManager');
    }

    public function testPersist(): void
    {
        $action = new Action();
        $action->setTitle('test-'.uniqid('', true));

        $this->actionManager->persist($action);

        $author = new Author();
        $author->setTitle('test-'.uniqid('', true));

        $this->authorManager->persist($author);

        $actionAuthor = new ActionAuthor();
        $actionAuthor->setAction($action);
        $actionAuthor->setAuthor($author);

        $this->actionAuthorManager->persist($actionAuthor);

        $this->assertIsInt($actionAuthor->getId());

        $this->actionAuthorManager->remove($actionAuthor);

        $this->authorManager->remove($author);

        $this->actionManager->remove($action);
    }

    public function testGetOne(): void
    {
        $test = $this->actionAuthorManager->getOne(['id' => 0]);
        $this->assertNull($test);
    }

    public function testGetList(): void
    {
        $test = $this->actionAuthorManager->getList(['id' => 0])->getResult();
        $this->assertIsArray($test);
        $this->assertCount(0, $test);
    }
}
