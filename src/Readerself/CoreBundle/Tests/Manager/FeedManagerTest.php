<?php
namespace Readerself\CoreBundle\Tests\Manager;

use Readerself\CoreBundle\Entity\Feed;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FeedManagerTest extends KernelTestCase
{
    protected $feedManager;

    protected function setUp()
    {
        self::bootKernel();

        $this->feedManager = static::$kernel->getContainer()->get('readerself_core_manager_feed');
    }

    public function test()
    {
        $feed = $this->feedManager->init();
        $feed->setTitle('test-'.uniqid('', true));
        $feed->setLink('test-'.uniqid('', true));

        $feed_id = $this->feedManager->persist($feed);

        $test = $this->feedManager->getOne(['id' => $feed_id]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(Feed::class, $test);

        $this->feedManager->remove($feed);

        $test = $this->feedManager->getOne(['id' => $feed_id]);
        $this->assertNull($test);
    }
}
