<?php
namespace Axipi\MCoreBundle\Tests\Manager;

use Readerself\CoreBundle\Entity\Item;
use Readerself\CoreBundle\Entity\Feed;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ItemManagerTest extends KernelTestCase
{
    protected $itemManager;

    protected $memberManager;

    protected $feedManager;

    protected function setUp()
    {
        self::bootKernel();

        $this->itemManager = static::$kernel->getContainer()->get('readerself_core_manager_item');

        $this->feedManager = static::$kernel->getContainer()->get('readerself_core_manager_feed');
    }

    public function test()
    {
        $feed = $this->feedManager->init();
        $feed->setTitle('test-unitaire-'.uniqid('', true));
        $feed->setLink('test-unitaire-'.uniqid('', true));

        $feed_id = $this->feedManager->persist($feed);

        $item = $this->itemManager->init();
        $item->setFeed($feed);
        $item->setTitle('test-unitaire-'.uniqid('', true));
        $item->setLink('test-unitaire-'.uniqid('', true));
        $item->setDate(new \Datetime());

        $item_id = $this->itemManager->persist($item);

        $test = $this->itemManager->getOne(['id' => $item_id]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(Item::class, $test);

        $this->itemManager->remove($item);

        $test = $this->itemManager->getOne(['id' => $item_id]);
        $this->assertNull($test);

        $this->feedManager->remove($feed);
    }
}
