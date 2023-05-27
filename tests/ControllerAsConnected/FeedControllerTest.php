<?php

namespace App\Tests\ControllerAsConnected;

use App\Tests\ControllerAsConnected\AbstractControllerTest;

class FeedControllerTest extends AbstractControllerTest
{
    public function testIndex(): void
    {
        $this->client->request('GET', '/api/feeds');
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
            'link' => $test,
        ];
        $this->client->request('POST', '/api/feeds', [], [], [], json_encode($data));
        $json = $this->client->getResponse()->getContent();
        $isValidResponseString = $this->isValidResponseString($json);
        $content = json_decode($json, true);

        $this->assertTrue($isValidResponseString);
        $this->assertResponseStatusCodeSame(201);
        $this->assertEquals('feed', $content['data']['type']);
        $this->assertEquals($test, $content['data']['attributes']['title']);
        $this->assertEquals($test, $content['data']['attributes']['link']);
    }

    public function testRead404(): void
    {
        $this->client->request('GET', '/api/feed/0');
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
        if ($id = $this->retrieveOneId('/api/feeds?filter[link]=phpunit-%')) {
            $this->client->request('GET', '/api/feed/'.$id);
            $json = $this->client->getResponse()->getContent();
            $isValidResponseString = $this->isValidResponseString($json);
            $content = json_decode($json, true);

            $this->assertTrue($isValidResponseString);
            $this->assertResponseStatusCodeSame(200);
            $this->assertEquals('feed', $content['data']['type']);
            $this->assertEquals($id, $content['data']['id']);
        }
    }

    public function testUpdate404(): void
    {
        $this->client->request('PUT', '/api/feed/0');
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
        if ($id = $this->retrieveOneId('/api/feeds?filter[link]=phpunit-%')) {
            $test = uniqid('phpunit-');

            $data = [
                'title' => $test,
                'link' => $test,
            ];
            $this->client->request('PUT', '/api/feed/'.$id, [], [], [], json_encode($data));
            $json = $this->client->getResponse()->getContent();
            $isValidResponseString = $this->isValidResponseString($json);
            $content = json_decode($json, true);

            $this->assertTrue($isValidResponseString);
            $this->assertResponseStatusCodeSame(200);
            $this->assertEquals('feed', $content['data']['type']);
            $this->assertEquals($id, $content['data']['id']);
            $this->assertEquals($test, $content['data']['attributes']['title']);
            $this->assertEquals($test, $content['data']['attributes']['link']);
        }
    }

    public function testDelete404(): void
    {
        $this->client->request('DELETE', '/api/feed/0');
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
        if ($id = $this->retrieveOneId('/api/feeds?filter[link]=phpunit-%')) {
            $this->client->request('DELETE', '/api/feed/'.$id);
            $json = $this->client->getResponse()->getContent();
            $isValidResponseString = $this->isValidResponseString($json);
            $content = json_decode($json, true);

            $this->assertTrue($isValidResponseString);
            $this->assertResponseStatusCodeSame(200);
            $this->assertEquals('feed', $content['data']['type']);
            $this->assertEquals($id, $content['data']['id']);
        }
    }
}
