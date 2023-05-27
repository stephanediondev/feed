<?php

namespace App\Tests\ControllerAsConnected;

use App\Tests\ControllerAsConnected\AbstractControllerTest;

class AuthorControllerTest extends AbstractControllerTest
{
    public function testIndex(): void
    {
        $this->client->request('GET', '/api/authors');
        $json = $this->client->getResponse()->getContent();
        $isValidResponseString = $this->isValidResponseString($json);

        $this->assertTrue($isValidResponseString);
        $this->assertResponseStatusCodeSame(200);
    }

    public function testCreate()
    {
        $test = uniqid('phpunit-');

        $data = [
            'title' => $test,
        ];
        $this->client->request('POST', '/api/authors', [], [], [], json_encode($data));
        $json = $this->client->getResponse()->getContent();
        $isValidResponseString = $this->isValidResponseString($json);
        $content = json_decode($json, true);

        $this->assertTrue($isValidResponseString);
        $this->assertResponseStatusCodeSame(201);
        $this->assertEquals('author', $content['data']['type']);
        $this->assertEquals($test, $content['data']['attributes']['title']);
    }

    public function testRead404(): void
    {
        $this->client->request('GET', '/api/author/0');
        $json = $this->client->getResponse()->getContent();
        $isValidResponseString = $this->isValidResponseString($json);
        $content = json_decode($json, true);

        $this->assertTrue($isValidResponseString);
        $this->assertResponseStatusCodeSame(404);
        $this->assertEquals('404', $content['errors'][0]['status']);
        $this->assertEquals('Not Found', $content['errors'][0]['title']);
    }

    public function testRead(): void
    {
        if ($id = $this->retrieveOneId('/api/authors?filter[title]=phpunit-%')) {
            $this->client->request('GET', '/api/author/'.$id);
            $json = $this->client->getResponse()->getContent();
            $isValidResponseString = $this->isValidResponseString($json);
            $content = json_decode($json, true);

            $this->assertTrue($isValidResponseString);
            $this->assertResponseStatusCodeSame(200);
            $this->assertEquals('author', $content['data']['type']);
            $this->assertEquals($id, $content['data']['id']);
        }
    }

    public function testUpdate404(): void
    {
        $this->client->request('PUT', '/api/author/0');
        $json = $this->client->getResponse()->getContent();
        $isValidResponseString = $this->isValidResponseString($json);
        $content = json_decode($json, true);

        $this->assertTrue($isValidResponseString);
        $this->assertResponseStatusCodeSame(404);
        $this->assertEquals('404', $content['errors'][0]['status']);
        $this->assertEquals('Not Found', $content['errors'][0]['title']);
    }

    public function testUpdate(): void
    {
        if ($id = $this->retrieveOneId('/api/authors?filter[title]=phpunit-%')) {
            $test = uniqid('phpunit-');

            $data = [
                'title' => $test,
            ];
            $this->client->request('PUT', '/api/author/'.$id, [], [], [], json_encode($data));
            $json = $this->client->getResponse()->getContent();
            $isValidResponseString = $this->isValidResponseString($json);
            $content = json_decode($json, true);

            $this->assertTrue($isValidResponseString);
            $this->assertResponseStatusCodeSame(200);
            $this->assertEquals('author', $content['data']['type']);
            $this->assertEquals($id, $content['data']['id']);
            $this->assertEquals($test, $content['data']['attributes']['title']);
        }
    }

    public function testDelete404(): void
    {
        $this->client->request('DELETE', '/api/author/0');
        $json = $this->client->getResponse()->getContent();
        $isValidResponseString = $this->isValidResponseString($json);
        $content = json_decode($json, true);

        $this->assertTrue($isValidResponseString);
        $this->assertResponseStatusCodeSame(404);
        $this->assertEquals('404', $content['errors'][0]['status']);
        $this->assertEquals('Not Found', $content['errors'][0]['title']);
    }

    public function testDelete(): void
    {
        if ($id = $this->retrieveOneId('/api/authors?filter[title]=phpunit-%')) {
            $this->client->request('DELETE', '/api/author/'.$id);
            $json = $this->client->getResponse()->getContent();
            $isValidResponseString = $this->isValidResponseString($json);
            $content = json_decode($json, true);

            $this->assertTrue($isValidResponseString);
            $this->assertResponseStatusCodeSame(200);
            $this->assertEquals('author', $content['data']['type']);
            $this->assertEquals($id, $content['data']['id']);
        }
    }
}
