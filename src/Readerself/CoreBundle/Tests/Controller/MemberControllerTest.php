<?php
namespace Readerself\CoreBundle\Tests\Controller;

class MemberControllerTest extends AbstractControllerTest
{
    public function testCreate()
    {
        // test 403
        $this->client->request('POST', '/api/member');
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }

    public function testRead()
    {
        // test 403
        $this->client->request('GET', '/api/member/0');
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));

        // test 404
        $this->client->request('GET', '/api/member/0', [], [], ['HTTP_X-CONNECTION-TOKEN' => $this->token]);
        $response = $this->client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }

    public function testUpdate()
    {
        // test 403
        $this->client->request('PUT', '/api/member/0');
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));

        // test 404
        $this->client->request('PUT', '/api/member/0', [], [], ['HTTP_X-CONNECTION-TOKEN' => $this->token]);
        $response = $this->client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }

    public function testDelete()
    {
        // test 403
        $this->client->request('DELETE', '/api/member/0');
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));

        // test 404
        $this->client->request('DELETE', '/api/member/0', [], [], ['HTTP_X-CONNECTION-TOKEN' => $this->token]);
        $response = $this->client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }

    public function testLogin()
    {
        // test 401
        $this->client->request('POST', '/api/login');
        $response = $this->client->getResponse();

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }

    public function testProfile()
    {
        // test 403
        $this->client->request('POST', '/api/profile');
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));

        // test GET
        $this->client->request('GET', '/api/profile', [], [], ['HTTP_X-CONNECTION-TOKEN' => $this->token]);
        $response = $this->client->getResponse();

        $content = json_decode($response->getContent(), true);

        $this->assertEquals('member', $content['entry_entity']);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }

    public function testLogout403()
    {
        // test 403
        $this->client->request('GET', '/api/logout');
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }
}
