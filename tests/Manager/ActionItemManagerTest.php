<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Entity\Action;
use App\Entity\ActionItem;
use App\Entity\Feed;
use App\Entity\Item;
use App\Manager\ActionItemManager;
use App\Manager\ActionManager;
use App\Manager\FeedManager;
use App\Manager\ItemManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ActionItemManagerTest extends KernelTestCase
{
    protected FeedManager $feedManager;

    protected ActionManager $actionManager;

    protected ItemManager $itemManager;

    protected ActionItemManager $actionItemManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->feedManager = static::getContainer()->get('App\Manager\FeedManager');

        $this->actionManager = static::getContainer()->get('App\Manager\ActionManager');

        $this->itemManager = static::getContainer()->get('App\Manager\ItemManager');

        $this->actionItemManager = static::getContainer()->get('App\Manager\ActionItemManager');
    }

    public function testPersist(): void
    {
        $action = new Action();
        $action->setTitle('test-'.uniqid('', true));

        $this->actionManager->persist($action);

        $feed = new Feed();
        $feed->setTitle('test-'.uniqid('', true));
        $feed->setLink('test-'.uniqid('', true));

        $this->feedManager->persist($feed);

        $item = new Item();
        $item->setFeed($feed);
        $item->setTitle('test-'.uniqid('', true));
        $item->setLink('test-'.uniqid('', true));
        $item->setDate(new \Datetime());

        $this->itemManager->persist($item);

        $actionItem = new ActionItem();
        $actionItem->setAction($action);
        $actionItem->setItem($item);

        $this->actionItemManager->persist($actionItem);

        $this->assertIsInt($actionItem->getId());

        $this->actionItemManager->remove($actionItem);

        $this->itemManager->remove($item);

        $this->actionManager->remove($action);

        $this->feedManager->remove($feed);
    }

    public function testGetOne(): void
    {
        $test = $this->actionItemManager->getOne(['id' => 0]);
        $this->assertNull($test);
    }

    public function testGetList(): void
    {
        $test = $this->actionItemManager->getList(['id' => 0])->getResult();
        $this->assertIsArray($test);
        $this->assertCount(0, $test);
    }
}
