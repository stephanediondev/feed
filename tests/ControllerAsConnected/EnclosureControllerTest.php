<?php

namespace App\Tests\ControllerAsConnected;

use App\Tests\ControllerAsConnected\AbstractControllerTest;

class EnclosureControllerTest extends AbstractControllerTest
{
    public function testIndex(): void
    {
        $this->client->request('GET', '/api/enclosures');

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }
}
