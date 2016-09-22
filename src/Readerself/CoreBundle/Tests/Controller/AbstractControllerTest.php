<?php
namespace Readerself\CoreBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractControllerTest extends WebTestCase
{
    protected $client;

    protected $token;

    public function __construct() {
        $this->client = static::createClient();

        $this->token = 'ggYzVnCV3+f55TTWwnaKtkzyPhY7YqbGodhzsfiJE8ze+FugfmKN3Pg/VuQtugiLjK0=';
    }
}
