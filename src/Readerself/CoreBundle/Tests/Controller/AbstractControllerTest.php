<?php
namespace Readerself\CoreBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractControllerTest extends WebTestCase
{
    protected $client;

    protected $token;

    public function __construct() {
        $this->client = static::createClient();

        $this->token = 'j5ybdQUKGYug2AzhEzR6tyn7gxJsGwdAmAw/OolHhOYw5kIq0G2xg/WU2A5oaW6x8bg=';
    }
}
