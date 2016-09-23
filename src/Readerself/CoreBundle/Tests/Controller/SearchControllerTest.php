<?php
namespace Readerself\CoreBundle\Tests\Controller;

class SearchControllerTest extends AbstractControllerTest
{
    public function testFeeds()
    {
        // test GET
        $this->client->request('GET', '/api/feeds/search');
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }

    public function testItems()
    {
        // test GET
        $this->client->request('GET', '/api/items/search');
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }

    public function testCategories()
    {
        // test GET
        $this->client->request('GET', '/api/categories/search');
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }
}
