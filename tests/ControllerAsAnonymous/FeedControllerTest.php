<?php

namespace App\Tests\ControllerAsAnonymous;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FeedControllerTest extends WebTestCase
{
    protected $client;

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
}
