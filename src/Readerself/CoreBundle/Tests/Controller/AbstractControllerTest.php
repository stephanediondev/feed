<?php
namespace Readerself\CoreBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Readerself\CoreBundle\Entity\Member;

abstract class AbstractControllerTest extends WebTestCase
{
    protected $memberManager;

    protected $memberId;

    protected $memberAdministratorId;

    protected $client;

    protected $token;

    protected $tokenAdministrator;

    public function __construct() {
        $this->client = static::createClient();

        $this->memberManager = $this->client->getContainer()->get('readerself_core_manager_member');

        //member not administrator
        $member = $this->memberManager->init();
        $member->setEmail('test-'.uniqid('', true));
        $member->setPassword('test-'.uniqid('', true));
        $member->setAdministrator(false);
        $this->memberId = $this->memberManager->persist($member);
        $member = $this->memberManager->getOne(['id' => $this->memberId]);

        $this->token = base64_encode(random_bytes(50));
        $connection = $this->memberManager->connectionManager->init();
        $connection->setMember($member);
        $connection->setType('login');
        $connection->setToken($this->token);
        $this->memberManager->connectionManager->persist($connection);

        //member administrator
        $member = $this->memberManager->init();
        $member->setEmail('test-'.uniqid('', true));
        $member->setPassword('test-'.uniqid('', true));
        $member->setAdministrator(true);
        $this->memberAdministratorId = $this->memberManager->persist($member);
        $member = $this->memberManager->getOne(['id' => $this->memberAdministratorId]);

        $this->tokenAdministrator = base64_encode(random_bytes(50));
        $connection = $this->memberManager->connectionManager->init();
        $connection->setMember($member);
        $connection->setType('login');
        $connection->setToken($this->tokenAdministrator);
        $this->memberManager->connectionManager->persist($connection);
    }

    public function __destruct() {
        $member = $this->memberManager->getOne(['id' => $this->memberId]);
        $this->memberManager->remove($member);

        $member = $this->memberManager->getOne(['id' => $this->memberAdministratorId]);
        $this->memberManager->remove($member);
    }
}
