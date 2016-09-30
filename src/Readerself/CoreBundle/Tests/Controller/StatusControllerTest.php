<?php
namespace Readerself\CoreBundle\Tests\Controller;

class StatusControllerTest extends AbstractControllerTest
{
    public function testIndex()
    {
        // test 403
        $this->client->request('GET', '/api/status');
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));

        // test 403 not administrator
        $this->client->request('GET', '/api/status', [], [], ['HTTP_X-CONNECTION-TOKEN' => $this->token]);
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));

        // test 200 administrator
        $this->client->request('GET', '/api/status', [], [], ['HTTP_X-CONNECTION-TOKEN' => $this->tokenAdministrator]);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }
}
