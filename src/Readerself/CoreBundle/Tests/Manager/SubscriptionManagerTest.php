<?php
namespace Axipi\MCoreBundle\Tests\Manager;

use Readerself\CoreBundle\Entity\Subscription;
use Readerself\CoreBundle\Entity\Feed;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SubscriptionManagerTest extends KernelTestCase
{
    protected $subscriptionManager;

    protected $memberManager;

    protected $feedManager;

    protected function setUp()
    {
        self::bootKernel();

        $this->subscriptionManager = static::$kernel->getContainer()->get('readerself_core_manager_subscription');

        $this->memberManager = static::$kernel->getContainer()->get('readerself_core_manager_member');

        $this->feedManager = static::$kernel->getContainer()->get('readerself_core_manager_feed');
    }

    public function test()
    {
        $feed = $this->feedManager->init();
        $feed->setTitle('test-unitaire-'.uniqid('', true));
        $feed->setLink('test-unitaire-'.uniqid('', true));

        $feed_id = $this->feedManager->persist($feed);

        $subscription = $this->subscriptionManager->init();
        $subscription->setMember($this->memberManager->getOne());
        $subscription->setFeed($feed);

        $subscription_id = $this->subscriptionManager->persist($subscription);

        $test = $this->subscriptionManager->getOne(['id' => $subscription_id]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(Subscription::class, $test);

        $this->subscriptionManager->remove($subscription);

        $test = $this->subscriptionManager->getOne(['id' => $subscription_id]);
        $this->assertNull($test);

        $this->feedManager->remove($feed);
    }
}
