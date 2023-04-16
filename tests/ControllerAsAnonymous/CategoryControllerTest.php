<?php

namespace App\Tests\ControllerAsAnonymous;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CategoryControllerTest extends WebTestCase
{
    protected $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testIndex(): void
    {
        $this->client->request('GET', '/api/categories');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCreate(): void
    {
        $this->client->request('POST', '/api/categories');

        $this->assertResponseStatusCodeSame(401);
    }
}
