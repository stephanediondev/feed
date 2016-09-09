<?php
namespace Axipi\MCoreBundle\Tests\Manager;

use Readerself\CoreBundle\Entity\Enclosure;
use Readerself\CoreBundle\Entity\Item;
use Readerself\CoreBundle\Entity\Feed;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EnclosureManagerTest extends KernelTestCase
{
    protected $enclosureManager;

    protected $itemManager;

    protected $feedManager;

    protected function setUp()
    {
        self::bootKernel();

        $this->enclosureManager = static::$kernel->getContainer()->get('readerself_core_manager_enclosure');

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

        $enclosure = $this->enclosureManager->init();
        $enclosure->setItem($item);
        $enclosure->setLink('test-unitaire-'.uniqid('', true));
        $enclosure->setType('test-unitaire-'.uniqid('', true));

        $enclosure_id = $this->enclosureManager->persist($enclosure);

        $test = $this->enclosureManager->getOne(['id' => $enclosure_id]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(Enclosure::class, $test);

        $this->enclosureManager->remove($enclosure);

        $test = $this->itemManager->getOne(['id' => $enclosure_id]);
        $this->assertNull($test);

        $this->itemManager->remove($item);

        $this->feedManager->remove($feed);
    }
}
