<?php

namespace App\Tests\ControllerAsAnonymous;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class FeedControllerTest extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testIndex(): void
    {
        $this->client->request('GET', '/api/feeds');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCreate(): void
    {
        $this->client->request('POST', '/api/feeds');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testRead(): void
    {
        $this->client->request('GET', '/api/feed/0');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testUpdate(): void
    {
        $this->client->request('PUT', '/api/feed/0');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testDelete(): void
    {
        $this->client->request('DELETE', '/api/feed/0');

        $this->assertResponseStatusCodeSame(401);
    }
}
