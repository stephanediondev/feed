<?php
namespace Readerself\CoreBundle\Tests\Manager;

use Readerself\CoreBundle\Entity\Enclosure;
use Readerself\CoreBundle\Entity\Item;
use Readerself\CoreBundle\Entity\Feed;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EnclosureManagerTest extends KernelTestCase
{
    protected $itemManager;

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
        $feed->setTitle('test-'.uniqid('', true));
        $feed->setLink('test-'.uniqid('', true));

        $feed_id = $this->feedManager->persist($feed);

        $item = $this->itemManager->init();
        $item->setFeed($feed);
        $item->setTitle('test-'.uniqid('', true));
        $item->setLink('test-'.uniqid('', true));
        $item->setDate(new \Datetime());

        $item_id = $this->itemManager->persist($item);

        $enclosure = $this->itemManager->enclosureManager->init();
        $enclosure->setItem($item);
        $enclosure->setLink('test-'.uniqid('', true));
        $enclosure->setType('test-'.uniqid('', true));

        $enclosure_id = $this->itemManager->enclosureManager->persist($enclosure);

        $test = $this->itemManager->enclosureManager->getOne(['id' => $enclosure_id]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(Enclosure::class, $test);

        $this->itemManager->enclosureManager->remove($enclosure);

        $test = $this->itemManager->getOne(['id' => $enclosure_id]);
        $this->assertNull($test);

        $this->itemManager->remove($item);

        $this->feedManager->remove($feed);
    }
}
