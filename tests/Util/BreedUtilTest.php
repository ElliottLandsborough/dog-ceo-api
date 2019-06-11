<?php
// tests/Util/BreedUtilTest.php
namespace App\Tests\Util;

use App\Util\BreedUtil;
use PHPUnit\Framework\TestCase;

class BreedUtilTest extends TestCase
{
    protected $util;

    // runs per test
    public function setUp()
    {
        $this->util = new BreedUtil();

        // disable the cache
        $this->util->clearCache();

        // set the client to use the mock api
        $this->util->setClient(new \App\Util\MockApi());
    }

    public function testGetAllBreeds()
    {
        $response = $this->util->getAllBreeds()->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals('success', json_decode($response->getContent())->status);

        $this->assertArrayHasKey('bullterrier', (array) json_decode($response->getContent())->message);
    }

    public function testGetAllTopLevelBreeds()
    {
        $response = $this->util->getAllTopLevelBreeds()->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals('success', json_decode($response->getContent())->status);

        $this->assertContains('bullterrier', (array) json_decode($response->getContent())->message);
    }

    public function testGetAllSubBreeds()
    {
        $response = $this->util->getAllSubBreeds('affenpinscher')->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', json_decode($response->getContent())->status);
        $this->assertEmpty((array) json_decode($response->getContent())->message);

        $response = $this->util->getAllSubBreeds('bullterrier')->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', json_decode($response->getContent())->status);
        $this->assertContains('staffordshire', (array) json_decode($response->getContent())->message);
        
        $response = $this->util->getAllSubBreeds('DOESNOTEXIST')->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('error', json_decode($response->getContent())->status);
    }

    public function testGetTopLevelImages()
    {
        $response = $this->util->getTopLevelImages('affenpinscher')->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', json_decode($response->getContent())->status);
        $this->assertGreaterThan(0, count((array) json_decode($response->getContent())->message));
        $string = 'https://images.dog.ceo';
        $this->assertEquals($string, substr(json_decode($response->getContent())->message[0], 0, strlen($string)));

        $response = $this->util->getTopLevelImages('DOESNOTEXIST')->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('error', json_decode($response->getContent())->status);
    }

    public function testGetRandomTopLevelImage()
    {
        $response = $this->util->getRandomTopLevelImage('affenpinscher')->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', json_decode($response->getContent())->status);
        $string = 'https://images.dog.ceo';
        $this->assertEquals($string, substr(json_decode($response->getContent())->message, 0, strlen($string)));

        $response = $this->util->getRandomTopLevelImage('DOESNOTEXIST')->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('error', json_decode($response->getContent())->status);
    }

    public function testGetRandomTopLevelImages()
    {
        $response = $this->util->getRandomTopLevelImages('affenpinscher', 3)->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', json_decode($response->getContent())->status);
        $this->assertGreaterThan(0, count((array) json_decode($response->getContent())->message));
        $string = 'https://images.dog.ceo';
        $this->assertEquals($string, substr(json_decode($response->getContent())->message[0], 0, strlen($string)));

        $response = $this->util->getRandomTopLevelImages('DOESNOTEXIST', 3)->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('error', json_decode($response->getContent())->status);
    }

    public function testGetSubLevelImages()
    {
        $response = $this->util->getSubLevelImages('bullterrier', 'staffordshire')->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', json_decode($response->getContent())->status);
        $this->assertGreaterThan(0, count((array) json_decode($response->getContent())->message));
        $string = 'https://images.dog.ceo';
        $this->assertEquals($string, substr(json_decode($response->getContent())->message[0], 0, strlen($string)));

        $response = $this->util->getSubLevelImages('DOESNOTEXIST', 'DOESNOTEXIST')->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('error', json_decode($response->getContent())->status);

        $response = $this->util->getSubLevelImages('bullterrier', 'DOESNOTEXIST')->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('error', json_decode($response->getContent())->status);
    }

    public function getRandomSubLevelImage()
    {
        $response = $this->util->getRandomSubLevelImage('bullterrier', 'staffordshire')->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', json_decode($response->getContent())->status);
        $string = 'https://images.dog.ceo';
        $this->assertEquals($string, substr(json_decode($response->getContent())->message, 0, strlen($string)));

        $response = $this->util->getRandomSubLevelImage('DOESNOTEXIST', 'DOESNOTEXIST')->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('error', json_decode($response->getContent())->status);

        $response = $this->util->getRandomSubLevelImage('bullterrier', 'DOESNOTEXIST')->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('error', json_decode($response->getContent())->status);
    }

    public function testGetRandomSubLevelImages()
    {
        $response = $this->util->getRandomSubLevelImages('bullterrier', 'staffordshire', 3)->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', json_decode($response->getContent())->status);
        $this->assertGreaterThan(0, count((array) json_decode($response->getContent())->message));
        $string = 'https://images.dog.ceo';
        $this->assertEquals($string, substr(json_decode($response->getContent())->message[0], 0, strlen($string)));

        $response = $this->util->getRandomSubLevelImages('DOESNOTEXIST', 'DOESNOTEXIST', 3)->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('error', json_decode($response->getContent())->status);

        $response = $this->util->getRandomSubLevelImages('bullterrier', 'DOESNOTEXIST', 3)->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('error', json_decode($response->getContent())->status);
    }

    public function testGetRandomImage()
    {
        $response = $this->util->getRandomImage()->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', json_decode($response->getContent())->status);
        $string = 'https://images.dog.ceo';
        $this->assertEquals($string, substr(json_decode($response->getContent())->message, 0, strlen($string)));
    }

    public function testGetRandomImages()
    {
        $response = $this->util->getRandomImages(3)->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', json_decode($response->getContent())->status);
        $this->assertGreaterThan(0, count((array) json_decode($response->getContent())->message));
        $string = 'https://images.dog.ceo';
        $this->assertEquals($string, substr(json_decode($response->getContent())->message[0], 0, strlen($string)));
    }

    public function testGetMasterText()
    {
        $response = $this->util->masterText('affenpinscher')->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', json_decode($response->getContent())->status);
        $this->assertArrayHasKey('name', (array) json_decode($response->getContent())->message);
        $this->assertArrayHasKey('info', (array) json_decode($response->getContent())->message);

        $response = $this->util->masterText('DOESNOTEXIST')->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('error', json_decode($response->getContent())->status);
    }

    public function testGetSubText()
    {
        $response = $this->util->subText('bullterrier', 'staffordshire')->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', json_decode($response->getContent())->status);
        $this->assertArrayHasKey('name', (array) json_decode($response->getContent())->message);
        $this->assertArrayHasKey('info', (array) json_decode($response->getContent())->message);

        $response = $this->util->subText('DOESNOTEXIST', 'DOESNOTEXIST')->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('error', json_decode($response->getContent())->status);

        $response = $this->util->subText('bullterrier', 'DOESNOTEXIST')->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('error', json_decode($response->getContent())->status);
    }

    public function testGetAllBreedsRandomSingle()
    {
        $response = $this->util->getAllBreedsRandomSingle()->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', json_decode($response->getContent())->status);
        $content = json_decode($response->getContent())->message;
        $first = reset($content);
        $this->assertEquals(is_array($first), true);
    }

    public function testGetAllBreedsRandomMultiple()
    {
        $response = $this->util->getAllBreedsRandomMultiple(3)->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', json_decode($response->getContent())->status);
        $content = json_decode($response->getContent())->message;
        $this->assertGreaterThan(0, count((array) $content));
        $first = reset($content);
        $this->assertEquals(is_array($first), true);
    }

    public function testGetAllTopLevelBreedsRandomSingle()
    {
        $response = $this->util->getAllTopLevelBreedsRandomSingle()->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', json_decode($response->getContent())->status);
        $content = json_decode($response->getContent())->message;
        $this->assertNotEmpty($content);
    }

    public function testGetAllTopLevelBreedsRandomMultiple()
    {
        $response = $this->util->getAllTopLevelBreedsRandomMultiple(3)->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', json_decode($response->getContent())->status);
        $content = json_decode($response->getContent())->message;
        $this->assertGreaterThan(1, count($content));
    }

    public function testGetAllSubBreedsRandomSingle()
    {
        $response = $this->util->getAllSubBreedsRandomSingle('bullterrier')->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', json_decode($response->getContent())->status);
        $content = json_decode($response->getContent())->message;
        $this->assertEquals('staffordshire', $content);

        $response = $this->util->getAllSubBreedsRandomSingle('DOESNOTEXIST')->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('error', json_decode($response->getContent())->status);
    }

    public function testGetAllSubBreedsRandomMulti()
    {
        $response = $this->util->getAllSubBreedsRandomMulti('bullterrier', 3)->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', json_decode($response->getContent())->status);
        $content = json_decode($response->getContent())->message;
        $this->assertGreaterThan(0, count((array) $content));
        $this->assertEquals('staffordshire', $content[0]);

        $response = $this->util->getAllSubBreedsRandomMulti('DOESNOTEXIST', 3)->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('error', json_decode($response->getContent())->status);
    }
}