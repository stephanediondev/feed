<?php

namespace App\Tests\ControllerAsAnonymous;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MemberPasskeyControllerTest extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testIndex(): void
    {
        $this->client->request('GET', '/api/passkeys');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testDelete(): void
    {
        $this->client->request('DELETE', '/api/passkey/0');

        $this->assertResponseStatusCodeSame(401);
    }
}
