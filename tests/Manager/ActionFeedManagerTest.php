<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Entity\Action;
use App\Entity\ActionFeed;
use App\Entity\Feed;
use App\Manager\ActionFeedManager;
use App\Manager\ActionManager;
use App\Manager\FeedManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ActionFeedManagerTest extends KernelTestCase
{
    protected ActionManager $actionManager;

    protected FeedManager $feedManager;

    protected ActionFeedManager $actionFeedManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->actionManager = static::getContainer()->get('App\Manager\ActionManager');

        $this->feedManager = static::getContainer()->get('App\Manager\FeedManager');

        $this->actionFeedManager = static::getContainer()->get('App\Manager\ActionFeedManager');
    }

    public function testPersist(): void
    {
        $action = new Action();
        $action->setTitle(uniqid('phpunit-'));

        $this->actionManager->persist($action);

        $feed = new Feed();
        $feed->setTitle(uniqid('phpunit-'));
        $feed->setLink(uniqid('phpunit-'));

        $this->feedManager->persist($feed);

        $actionFeed = new ActionFeed();
        $actionFeed->setAction($action);
        $actionFeed->setFeed($feed);

        $this->actionFeedManager->persist($actionFeed);

        $this->assertIsInt($actionFeed->getId());

        $this->actionFeedManager->remove($actionFeed);

        $this->feedManager->remove($feed);

        $this->actionManager->remove($action);
    }

    public function testGetOne(): void
    {
        $test = $this->actionFeedManager->getOne(['id' => 0]);
        $this->assertNull($test);
    }

    public function testGetList(): void
    {
        $test = $this->actionFeedManager->getList(['id' => 0])->getResult();
        $this->assertIsArray($test);
        $this->assertCount(0, $test);
    }
}
