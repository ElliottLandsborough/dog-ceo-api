<?php
// tests/Util/BreedUtilTest.php
namespace App\Tests\Util;

use App\Util\BreedUtil;
use PHPUnit\Framework\TestCase;

class BreedUtilTest extends TestCase
{
    protected $util;

    public function setUp()
    {
        $this->util = new BreedUtil();

        // disable the cache
        $this->util->disableCache();

        // set the client as the mock api one
        $this->util->setClient(new \App\Util\MockApi());
    }

    public function testGetAllBreeds()
    {
        $response = $this->util->getAllBreeds()->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals('success', json_decode($response->getContent())->status);

        $this->assertArrayHasKey('hound', (array) json_decode($response->getContent())->message);
    }
}