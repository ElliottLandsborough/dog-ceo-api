<?php

namespace controllers\tests;

use PHPUnit\Framework\TestCase;
use controllers\ApiController;

class ApiTest extends TestCase
{
    private $api;

    protected function SetUp()
    {
        $this->api = new ApiController();
    }

    protected function tearDown()
    {
        $this->api = false;
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method
     *
     * @return mixed Method return
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    public function testBreedsDirExists()
    {
        $result = $this->invokeMethod($this->api, 'getBreedsDirectory');
        $this->assertNotFalse($result);
    }

    public function testGetAllBreeds()
    {
        $result = $this->invokeMethod($this->api, 'getAllBreeds');
        $this->assertArrayHasKey('spaniel', $result);
        $this->assertContains('cocker', $result['spaniel']);
    }

    public function testGetMasterBreeds()
    {
        $result = $this->invokeMethod($this->api, 'getMasterBreeds');
        $this->assertContains('spaniel', $result);
    }

    public function testGetSubBreeds()
    {
        $result = $this->invokeMethod($this->api, 'getSubBreeds', ['breed' => 'spaniel']);
        $this->assertContains('cocker', $result);
    }

    public function testBreedListAll()
    {
        $response = $this->api->breedListAll();
        $this->assertNotContains('"code":"404"', $response->getContent());
    }

    public function testBreedList()
    {
        $response = $this->api->breedList();
        $this->assertContains('spaniel', $response->getContent());
    }

    public function testBreedListSub()
    {
        $response = $this->api->breedListSub('spaniel');
        $this->assertNotContains('"code":"404"', $response->getContent());
        $this->assertContains('cocker', $response->getContent());
    }

    public function testCleanBreedSubDir()
    {
        $start = $this->invokeMethod($this->api, 'cleanBreedSubDir', ['string' => 'spaniel-cocker']);
        $finish = 'spaniel/cocker';
        $this->assertEquals($start, $finish);
    }

    public function testMatchBreedString()
    {
        $result = $this->invokeMethod($this->api, 'matchBreedString', ['string' => 'spaniel']);
        $this->assertNotEmpty($result);

        $result = $this->invokeMethod($this->api, 'matchBreedString', ['string' => 'spaniel', 'string2' => 'cocker']);
        $this->assertNotEmpty($result);
        $this->assertNotFalse($result);

        $result = $this->invokeMethod($this->api, 'matchBreedString', ['string' => null, 'string2' => 'cocker']);
        $this->assertFalse($result);

        $result = $this->invokeMethod($this->api, 'matchBreedString', ['string' => null, 'string2' => null]);
        $this->assertFalse($result);
    }

    public function testGetBreedDirs()
    {
        $result = $this->invokeMethod($this->api, 'getBreedDirs');
        $this->assertNotEmpty($result);
    }

    public function testGetAllImages()
    {
        $dirs = $this->invokeMethod($this->api, 'getBreedDirs');
        $dir = $dirs[0];
        $result = $this->invokeMethod($this->api, 'getAllImages', ['imagesDir' => $dir]);
        $this->assertNotEmpty($result);
    }

    public function testGetRandomImage()
    {
        $dirs = $this->invokeMethod($this->api, 'getBreedDirs');
        $dir = $dirs[0];
        $result = $this->invokeMethod($this->api, 'getRandomImage', ['imagesDir' => $dir]);
        $this->assertNotEmpty($result);
    }

    public function testBreedImage()
    {
        $response = $this->invokeMethod($this->api, 'breedImage', ['spaniel']);
        $this->assertNotContains('"code":"404"', $response->getContent());

        $response = $this->invokeMethod($this->api, 'breedImage', [null, 'cocker']);
        $this->assertContains('"code":"404"', $response->getContent());
    }

    public function testBreedImage2()
    {
        $response = $this->invokeMethod($this->api, 'breedImage', ['spaniel', null, true]);
        $this->assertNotContains('"code":"404"', $response->getContent());
    }

    public function testBreedAllRandomImage()
    {
        $response = $this->api->breedAllRandomImage();
        $this->assertContains('"status":"success"', $response->getContent());
    }

    public function testBreedYamlFile()
    {
        $result = $this->invokeMethod($this->api, 'breedYamlFile', ['spaniel', null]);
        $this->assertNotEmpty($result);

        $result = $this->invokeMethod($this->api, 'breedYamlFile', ['spaniel', 'cocker']);
        $this->assertNotEmpty($result);

        $result = $this->invokeMethod($this->api, 'breedYamlFile', [null, 'cocker']);
        $this->assertFalse($result);
    }

    public function testArrayWhiteList()
    {
        $result = $this->invokeMethod($this->api, 'arrayWhitelist', [['allowed' => true, 'disallowed' => false], ['allowed']]);
        $this->assertEquals($result, ['allowed' => true]);
    }

    public function testGetBreedText()
    {
        $result = $this->invokeMethod($this->api, 'getBreedText', ['spaniel', null]);
        $this->assertNotEmpty($result);

        $result = $this->invokeMethod($this->api, 'getBreedText', ['spaniel', 'cocker']);
        $this->assertNotEmpty($result);

        $result = $this->invokeMethod($this->api, 'getBreedText', [null, 'cocker']);
        $this->assertFalse($result);
    }

    public function testBreedText()
    {
        $response = $this->api->breedText('spaniel');
        $this->assertNotContains('"code":"404"', $response->getContent());

        $response = $this->api->breedText('spaniel', 'cocker');
        $this->assertNotContains('"code":"404"', $response->getContent());

        $response = $this->api->breedText(null, 'cocker');
        $this->assertContains('"code":"404"', $response->getContent());
    }
}
