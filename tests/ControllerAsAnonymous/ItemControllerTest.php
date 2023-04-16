<?php

namespace App\Tests\ControllerAsAnonymous;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class ItemControllerTest extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testIndex(): void
    {
        $this->client->request('GET', '/api/items');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testRead(): void
    {
        $this->client->request('GET', '/api/item/0');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testDelete(): void
    {
        $this->client->request('DELETE', '/api/item/0');

        $this->assertResponseStatusCodeSame(401);
    }
}
