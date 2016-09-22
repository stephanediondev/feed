<?php
namespace Readerself\CoreBundle\Tests\Controller;

class AuthorControllerTest extends AbstractControllerTest
{
    public function testIndex()
    {
        // test 403
        $this->client->request('GET', '/api/authors');
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));

        // test entries_entity
        $this->client->request('GET', '/api/authors', [], [], ['HTTP_X-CONNECTION-TOKEN' => $this->token]);
        $response = $this->client->getResponse();

        $content = json_decode($response->getContent(), true);

        $this->assertEquals('author', $content['entries_entity']);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }

    public function testCreate()
    {
        // test 403
        $this->client->request('POST', '/api/author');
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));

        $title = 'test-'.uniqid('', true);

        // test POST
        $this->client->request('POST', '/api/author', ['title' => $title], [], ['HTTP_X-CONNECTION-TOKEN' => $this->token]);
        $response = $this->client->getResponse();

        $content = json_decode($response->getContent(), true);

        $this->assertEquals($title, $content['entry']['title']);
        $this->assertEquals('author', $content['entry_entity']);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));

        // test GET
        $this->client->request('GET', '/api/author/'.$content['entry']['id'], [], [], ['HTTP_X-CONNECTION-TOKEN' => $this->token]);
        $response = $this->client->getResponse();

        $content = json_decode($response->getContent(), true);

        $this->assertEquals($title, $content['entry']['title']);
        $this->assertEquals('author', $content['entry_entity']);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));

        // test DELETE
        $this->client->request('DELETE', '/api/author/'.$content['entry']['id'], [], [], ['HTTP_X-CONNECTION-TOKEN' => $this->token]);
        $response = $this->client->getResponse();

        $content = json_decode($response->getContent(), true);

        $this->assertEquals($title, $content['entry']['title']);
        $this->assertEquals('author', $content['entry_entity']);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }

    public function testRead()
    {
        // test 403
        $this->client->request('GET', '/api/author/0');
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));

        // test 404
        $this->client->request('GET', '/api/author/0', [], [], ['HTTP_X-CONNECTION-TOKEN' => $this->token]);
        $response = $this->client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }

    public function testUpdate()
    {
        // test 403
        $this->client->request('PUT', '/api/author/0');
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));

        // test 404
        $this->client->request('PUT', '/api/author/0', [], [], ['HTTP_X-CONNECTION-TOKEN' => $this->token]);
        $response = $this->client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }

    public function testDelete()
    {
        // test 403
        $this->client->request('DELETE', '/api/author/0');
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));

        // test 404
        $this->client->request('DELETE', '/api/author/0', [], [], ['HTTP_X-CONNECTION-TOKEN' => $this->token]);
        $response = $this->client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }
}
