<?php

namespace App\Tests\ControllerAsAnonymous;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class EnclosureControllerTest extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testIndex(): void
    {
        $this->client->request('GET', '/api/enclosures');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testRead(): void
    {
        $this->client->request('GET', '/api/enclosure/0');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testDelete(): void
    {
        $this->client->request('DELETE', '/api/enclosure/0');

        $this->assertResponseStatusCodeSame(401);
    }
}
