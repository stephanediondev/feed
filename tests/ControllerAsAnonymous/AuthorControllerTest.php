<?php

namespace App\Tests\ControllerAsAnonymous;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthorControllerTest extends WebTestCase
{
    protected $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testIndex(): void
    {
        $this->client->request('GET', '/api/authors');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCreate(): void
    {
        $this->client->request('POST', '/api/authors');

        $this->assertResponseStatusCodeSame(401);
    }
}
