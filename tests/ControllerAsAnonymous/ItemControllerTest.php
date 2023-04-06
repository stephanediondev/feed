<?php

namespace App\Tests\ControllerAsAnonymous;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ItemControllerTest extends WebTestCase
{
    protected $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testIndex(): void
    {
        $this->client->request('GET', '/api/items');

        $this->assertResponseStatusCodeSame(401);
    }
}
