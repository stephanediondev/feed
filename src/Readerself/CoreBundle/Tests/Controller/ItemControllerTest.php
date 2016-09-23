<?php
namespace Readerself\CoreBundle\Tests\Controller;

class ItemControllerTest extends AbstractControllerTest
{
    public function testIndex()
    {
        // test GET
        $this->client->request('GET', '/api/items');
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));

        // test entries_entity
        $this->client->request('GET', '/api/items', [], [], ['HTTP_X-CONNECTION-TOKEN' => $this->token]);
        $response = $this->client->getResponse();

        $content = json_decode($response->getContent(), true);

        $this->assertEquals('item', $content['entries_entity']);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }

    public function testMarkallasread()
    {
        // test 403
        $this->client->request('GET', '/api/items/markallasread');
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }

    public function testRead()
    {
        // test 404
        $this->client->request('GET', '/api/item/read/0', [], [], ['HTTP_X-CONNECTION-TOKEN' => $this->token]);
        $response = $this->client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }

    public function testStar()
    {
        // test 403
        $this->client->request('GET', '/api/item/star/0');
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));

        // test 404
        $this->client->request('GET', '/api/item/star/0', [], [], ['HTTP_X-CONNECTION-TOKEN' => $this->token]);
        $response = $this->client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }
}
