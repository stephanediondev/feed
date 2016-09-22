<?php
namespace Readerself\CoreBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractControllerTest extends WebTestCase
{
    protected $client;

    protected $token;

    public function __construct() {
        $this->client = static::createClient();

        $this->token = 'FNb17VlYo0Vobeos7CtSf3arW8CF/TrlyklUABN2PguOIAHXF4E7IRPzO3bT02Kn7w0=';
    }
}
