<?php
namespace Readerself\CoreBundle\Tests\Controller;

class FeedControllerTest extends AbstractControllerTest
{
    public function testIndex()
    {
        // test 403
        $this->client->request('GET', '/api/feeds');
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));

        // test entries_entity
        $this->client->request('GET', '/api/feeds', [], [], ['HTTP_X-CONNECTION-TOKEN' => $this->token]);
        $response = $this->client->getResponse();

        $content = json_decode($response->getContent(), true);

        $this->assertEquals('feed', $content['entries_entity']);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }

    public function testCreate()
    {
        // test 403
        $this->client->request('POST', '/api/feed');
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }

    public function testRead()
    {
        // test 403
        $this->client->request('GET', '/api/feed/0');
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));

        // test 404
        $this->client->request('GET', '/api/feed/0', [], [], ['HTTP_X-CONNECTION-TOKEN' => $this->token]);
        $response = $this->client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }

    public function testUpdate()
    {
        // test 403
        $this->client->request('PUT', '/api/feed/0');
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));

        // test 404
        $this->client->request('PUT', '/api/feed/0', [], [], ['HTTP_X-CONNECTION-TOKEN' => $this->token]);
        $response = $this->client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }

    public function testDelete()
    {
        // test 403
        $this->client->request('DELETE', '/api/feed/0');
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));

        // test 404
        $this->client->request('DELETE', '/api/feed/0', [], [], ['HTTP_X-CONNECTION-TOKEN' => $this->token]);
        $response = $this->client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }

    public function testSubscribe()
    {
        // test 403
        $this->client->request('GET', '/api/feed/subscribe/0');
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));

        // test 404
        $this->client->request('GET', '/api/feed/subscribe/0', [], [], ['HTTP_X-CONNECTION-TOKEN' => $this->token]);
        $response = $this->client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }
}
