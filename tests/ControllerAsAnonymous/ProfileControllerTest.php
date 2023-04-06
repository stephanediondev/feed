<?php

namespace App\Tests\ControllerAsAnonymous;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProfileControllerTest extends WebTestCase
{
    protected $client;

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

    public function testUpdate(): void
    {
        $this->client->request('PUT', '/api/profile');

        $this->assertResponseStatusCodeSame(401);
    }
}
