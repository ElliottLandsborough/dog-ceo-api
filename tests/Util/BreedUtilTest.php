<?php

// tests/Util/BreedUtilTest.php

namespace App\Tests\Util;

use App\Util\BreedUtil;
use PHPUnit\Framework\TestCase;

class BreedUtilTest extends TestCase
{
    protected $util;

    // runs per test
    public function setUp(): void
    {
        $this->util = new BreedUtil();

        // disable the cache
        $this->util->clearCache();

        // set the client to use the mock api
        $this->util->setClient(new \App\Util\MockApi());
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    public function isValidXml($content)
    {
        $content = trim($content);
        if (empty($content)) {
            return false;
        }
        //html go to hell!
        if (stripos($content, '<!DOCTYPE html>') !== false) {
            return false;
        }
    
        libxml_use_internal_errors(true);
        simplexml_load_string($content);
        $errors = libxml_get_errors();          
        libxml_clear_errors();  
    
        return empty($errors);
    }

    public function testGetAllBreeds()
    {
        $response = $this->util->getAllBreeds()->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals('success', json_decode($response->getContent())->status);

        $this->assertArrayHasKey('bullterrier', (array) json_decode($response->getContent())->message);

        $xmlResponse = $this->util->getAllBreeds()->xmlOutputEnable()->getResponse()->getContent();

        $this->assertEquals(true, $this->isValidXml($xmlResponse));
    }

    public function testGetAllTopLevelBreeds()
    {
        $response = $this->util->getAllTopLevelBreeds()->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals('success', json_decode($response->getContent())->status);

        $this->assertContains('bullterrier', (array) json_decode($response->getContent())->message);

        $xmlResponse = $this->util->getAllTopLevelBreeds()->xmlOutputEnable()->getResponse()->getContent();

        $this->assertEquals(true, $this->isValidXml($xmlResponse));
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

        $response = $this->util->getTopLevelImagesWithAltTags('affenpinscher')->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', json_decode($response->getContent())->status);
        $this->assertGreaterThan(0, count((array) json_decode($response->getContent())->message));
        $string = 'https://images.dog.ceo';
        $content = json_decode($response->getContent())->message;
        $this->assertEquals('Affenpinscher dog', $content[0]->altText);
        $this->assertEquals('https://images.dog.ceo/breeds/affenpinscher/image.jpg', $content[0]->url);

        $xmlResponse = $this->util->getTopLevelImages('affenpinscher')->xmlOutputEnable()->getResponse()->getContent();
        $this->assertEquals(true, $this->isValidXml($xmlResponse));

        $xmlResponse = $this->util->getTopLevelImagesWithAltTags('affenpinscher')->xmlOutputEnable()->getResponse()->getContent();
        $this->assertEquals(true, $this->isValidXml($xmlResponse));
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

        $response = $this->util->getRandomTopLevelImagesWithAltTags('affenpinscher', 3)->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', json_decode($response->getContent())->status);
        $this->assertGreaterThan(0, count((array) json_decode($response->getContent())->message));
        $string = 'https://images.dog.ceo';
        $content = json_decode($response->getContent())->message;
        $this->assertEquals('Affenpinscher dog', $content[0]->altText);
        $this->assertEquals('https://images.dog.ceo/breeds/affenpinscher/image.jpg', $content[0]->url);
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

        $response = $this->util->getSubLevelImagesWithAltTags('bullterrier', 'staffordshire')->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', json_decode($response->getContent())->status);
        $this->assertGreaterThan(0, count((array) json_decode($response->getContent())->message));
        $string = 'https://images.dog.ceo';
        $content = json_decode($response->getContent())->message;
        $this->assertEquals('Staffordshire bullterrier dog', $content[0]->altText);
        $this->assertEquals('https://images.dog.ceo/breeds/bullterrier-staffordshire/image.jpg', $content[0]->url);
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

        $response = $this->util->getRandomSubLevelImagesWithAltTags('bullterrier', 'staffordshire', 3)->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', json_decode($response->getContent())->status);
        $this->assertGreaterThan(0, count((array) json_decode($response->getContent())->message));
        $string = 'https://images.dog.ceo';
        $content = json_decode($response->getContent())->message;
        $this->assertEquals('Staffordshire bullterrier dog', $content[0]->altText);
        $this->assertEquals('https://images.dog.ceo/breeds/bullterrier-staffordshire/image.jpg', $content[0]->url);
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

        $response = $this->util->getRandomImages(9999)->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', json_decode($response->getContent())->status);
        $this->assertGreaterThan(0, count((array) json_decode($response->getContent())->message));
        $string = 'https://images.dog.ceo';
        $this->assertEquals($string, substr(json_decode($response->getContent())->message[0], 0, strlen($string)));

        $response = $this->util->getRandomImagesWithAltTags(3)->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', json_decode($response->getContent())->status);
        $this->assertGreaterThan(0, count((array) json_decode($response->getContent())->message));
        $string = 'https://images.dog.ceo';
        $content = json_decode($response->getContent())->message;
        $this->assertEquals($string, substr($content[0]->url, 0, strlen($string)));
        $this->assertEquals(' dog', substr($content[0]->altText, -4));
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

    public function testXmlOutputEnable()
    {
        $result = $this->util->xmlOutputEnable();
        $this->assertAttributeEquals(true, 'xmlEnable', $result);
    }

    public function testSetEndpointUrl()
    {
        $result = $this->util->setEndpointUrl('string');
        $this->assertAttributeEquals('string', 'endpointUrl', $result);
    }

    public function testGetWithGuzzle()
    {
        // bad url
        $error = $this->invokeMethod($this->util, 'getWithGuzzle', ['https://domain.test']);
        $this->assertEquals('unitFail', $error->status);
        $this->assertEquals('URI does not exist in MockApi.php', $error->message);

        // \GuzzleHttp\Exception\ClientException
        $error = $this->invokeMethod($this->util, 'getWithGuzzle', ['ClientException']);
        $this->assertEquals('error', $error->status);
        $this->assertEquals('ClientException', $error->message);

        // good url
        $success = $this->invokeMethod($this->util, 'getWithGuzzle', ['breed/affenpinscher/list']);
        $this->assertEquals('success', $success->status);
        $this->assertEquals([], $success->message);
    }

    public function testGetRandomSubLevelImage()
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

        // dupe?
        $result = $this->invokeMethod($this->util, 'getRandomSubLevelImage', ['bullterrier', 'staffordshire']);
        $result = $this->invokeMethod($this->util, 'arrayResponse');
        $this->assertEquals('success', $result->status);
        $this->assertEquals('https://images.dog.ceo/breeds/bullterrier-staffordshire/image.jpg', $result->message);

        $result = $this->invokeMethod($this->util, 'getResponseWithCacheHeaders');
        $this->assertEquals('application/json', $result->headers->get('content-type'));
        $this->assertEquals('max-age=1800, s-maxage=21600', $result->headers->get('cache-control'));

        $result = $this->invokeMethod($this->util, 'xmlResponse');
        $this->assertEquals('application/xml', $result->headers->get('content-type'));
    }

    public function testArrayIsMultiDimensional()
    {
        $twoDimensional = ['key' => []];
        $result = $this->invokeMethod($this->util, 'arrayIsMultiDimensional', [$twoDimensional]);
        $this->assertEquals(true, $result);

        $oneDimensional = ['key' => ''];
        $result = $this->invokeMethod($this->util, 'arrayIsMultiDimensional', [$oneDimensional]);
        $this->assertEquals(false, $result);
    }

    public function testNiceBreedNameFromFolder()
    {
        $folder = "string1-string2";
        $result = $this->invokeMethod($this->util, 'niceBreedNameFromFolder', [$folder]);
        $this->assertEquals("String2 string1", $result);
    }

    public function testNiceBreedAltFromFolder()
    {
        $folder = "string1-string2";
        $result = $this->invokeMethod($this->util, 'niceBreedAltFromFolder', [$folder]);
        $this->assertEquals("String2 string1 dog", $result);
    }

    public function testBreedFolderFromUrl()
    {
        $url = "/api/border-collie/dog.jpg";
        $result = $this->invokeMethod($this->util, 'breedFolderFromUrl', [$url]);
        $this->assertEquals("border-collie", $result);
    }

    public function testRandomItemsFromArray()
    {
        $array = [1,2,3];
        $count = count($array);
        $result = $this->invokeMethod($this->util, 'randomItemsFromArray', [$array, -1]);
        $this->assertEquals($count, count($result));
    }
}
