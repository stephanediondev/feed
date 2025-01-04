<?php

namespace App\Tests\ControllerAsAnonymous;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProfileControllerTest extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testIndex(): void
    {
        $this->client->request('GET', '/api/profile');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testConnections(): void
    {
        $this->client->request('GET', '/api/profile/connections');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testPasskeys(): void
    {
        $this->client->request('GET', '/api/profile/passkeys');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testUpdate(): void
    {
        $this->client->request('PUT', '/api/profile');

        $this->assertResponseStatusCodeSame(401);
    }
}
