<?php
namespace Readerself\CoreBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Readerself\CoreBundle\Entity\Member;

abstract class AbstractControllerTest extends WebTestCase
{
    protected $memberManager;

    protected $memberId;

    protected $client;

    protected $token;

    public function __construct() {
        $this->client = static::createClient();

        $this->memberManager = $this->client->getContainer()->get('readerself_core_manager_member');

        $member = $this->memberManager->init();
        $member->setEmail('test-'.uniqid('', true));
        $member->setPassword('test-'.uniqid('', true));
        $member->setRole('member');

        $this->memberId = $this->memberManager->persist($member);

        $member = $this->memberManager->getOne(['id' => $this->memberId]);

        $this->token = base64_encode(random_bytes(50));

        $connection = $this->memberManager->connectionManager->init();
        $connection->setMember($member);
        $connection->setType('login');
        $connection->setToken($this->token);

        $this->memberManager->connectionManager->persist($connection);
    }

    public function __destruct() {
        $test = $this->memberManager->getOne(['id' => $this->memberId]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(Member::class, $test);

        $this->memberManager->remove($test);

        $test = $this->memberManager->getOne(['id' => $this->memberId]);
        $this->assertNull($test);
    }
}
