<?php

namespace App\Tests\ControllerAsAnonymous;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConnectionControllerTest extends WebTestCase
{
    protected $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testIndex(): void
    {
        $this->client->request('GET', '/api/connections');

        $this->assertResponseStatusCodeSame(401);
    }
}
