<?php

namespace App\Tests\ControllerAsAnonymous;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CategoryControllerTest extends WebTestCase
{
    protected KernelBrowser $client;

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

    public function testRead(): void
    {
        $this->client->request('GET', '/api/category/0');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testUpdate(): void
    {
        $this->client->request('PUT', '/api/category/0');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testDelete(): void
    {
        $this->client->request('DELETE', '/api/category/0');

        $this->assertResponseStatusCodeSame(401);
    }
}
