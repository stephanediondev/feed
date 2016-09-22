<?php
namespace Readerself\CoreBundle\Tests\Manager;

use Readerself\CoreBundle\Entity\Category;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CategoryControllerTest extends WebTestCase
{
    public function testCreate403()
    {
        $client = static::createClient();

        $client->request('POST', '/api/category', [], [], []);
        $response = $client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }

    public function testRead403()
    {
        $client = static::createClient();

        $client->request('GET', '/api/category/0', [], [], []);
        $response = $client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }

    public function testUpdate403()
    {
        $client = static::createClient();

        $client->request('PUT', '/api/category/0', [], [], []);
        $response = $client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }

    public function testDelete403()
    {
        $client = static::createClient();

        $client->request('DELETE', '/api/category/0', [], [], []);
        $response = $client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }

    public function testRead404()
    {
        $client = static::createClient();

        $client->request('GET', '/api/category/0', [], [], ['HTTP_X-CONNECTION-TOKEN' => 'j5ybdQUKGYug2AzhEzR6tyn7gxJsGwdAmAw/OolHhOYw5kIq0G2xg/WU2A5oaW6x8bg=']);
        $response = $client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }

    public function testUpdate404()
    {
        $client = static::createClient();

        $client->request('PUT', '/api/category/0', [], [], ['HTTP_X-CONNECTION-TOKEN' => 'j5ybdQUKGYug2AzhEzR6tyn7gxJsGwdAmAw/OolHhOYw5kIq0G2xg/WU2A5oaW6x8bg=']);
        $response = $client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }

    public function testDelete404()
    {
        $client = static::createClient();

        $client->request('DELETE', '/api/category/0', [], [], ['HTTP_X-CONNECTION-TOKEN' => 'j5ybdQUKGYug2AzhEzR6tyn7gxJsGwdAmAw/OolHhOYw5kIq0G2xg/WU2A5oaW6x8bg=']);
        $response = $client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }
}
