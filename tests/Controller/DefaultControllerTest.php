<?php

// tests/Util/DefaultControllerTest.php

namespace App\Tests\Util;

use App\Util\MockApi;
use App\Util\BreedUtil;
use App\Controller\DefaultController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

// todo: assert error codes
class DefaultControllerTest extends TestCase
{
    protected $util;
    protected $controller;

    public function setUp()
    {
        $this->util = new BreedUtil();
        $this->util->clearCache();
        $this->util->setClient(new MockApi());

        $this->controller = new DefaultController($this->util);
    }

    public function testGetAllBreeds()
    {
        $r = $this->controller->getAllBreeds();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $this->assertEquals('{"status":"success","message":{"affenpinscher":[],"bullterrier":["staffordshire"]}}', $r->getContent());
    }

    public function testGetAllBreedsRandomSingle()
    {
        $r = $this->controller->getAllBreedsRandomSingle();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("success", $status);
        $this->assertCount(1, (array) $message);
    }

    public function testGetAllBreedsRandomMultiple()
    {
        // 1 will return 1
        $n = 1;
        $r = $this->controller->getAllBreedsRandomMultiple($n);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("success", $status);
        $this->assertCount($n, (array) $message);

        // 2 will return 2
        $n = 2;
        $r = $this->controller->getAllBreedsRandomMultiple($n);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("success", $status);
        $this->assertCount($n, (array) $message);

        // 3 will return all which is 2
        $n = 3;
        $r = $this->controller->getAllBreedsRandomMultiple($n);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("success", $status);
        $this->assertCount(2, (array) $message);

        // -1 will return 10 (so all which is 2)
        $n = -1;
        $r = $this->controller->getAllBreedsRandomMultiple($n);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("success", $status);
        $this->assertCount(2, (array) $message);

        // (string) will return 1
        $n = "$$";
        $r = $this->controller->getAllBreedsRandomMultiple($n);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("success", $status);
        $this->assertCount(1, (array) $message);
    }

    public function testGetAllTopLevelBreeds()
    {
        $r = $this->controller->getAllTopLevelBreeds();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("success", $status);
        $this->assertEquals(['affenpinscher', 'bullterrier'], $message);
    }

    public function testGetAllTopLevelBreedsRandomSingle()
    {
        $r = $this->controller->getAllTopLevelBreedsRandomSingle();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("success", $status);
        $this->assertContains($message, ['affenpinscher', 'bullterrier']);
    }

    public function testGetAllTopLevelBreedsRandomMultiple()
    {
        $r = $this->controller->getAllTopLevelBreedsRandomMultiple(2);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("success", $status);
        $this->assertEquals($message, ['affenpinscher', 'bullterrier']);

        $r = $this->controller->getAllTopLevelBreedsRandomMultiple(-1);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("success", $status);
        $this->assertEquals($message, ['affenpinscher', 'bullterrier']);

        $r = $this->controller->getAllTopLevelBreedsRandomMultiple(9999);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("success", $status);
        $this->assertEquals($message, ['affenpinscher', 'bullterrier']);

        $r = $this->controller->getAllTopLevelBreedsRandomMultiple("$$");
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("success", $status);
        $this->assertContains($message[0], ['affenpinscher', 'bullterrier']);
    }

    public function testGetAllSubBreeds()
    {
        $r = $this->controller->getAllSubBreeds('affenpinscher');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("success", $status);
        $this->assertCount(0, (array) $message);

        $r = $this->controller->getAllSubBreeds('bullterrier');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("success", $status);
        $this->assertEquals(["staffordshire"], (array) $message);

        $r = $this->controller->getAllSubBreeds('BAD');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("error", $status);
        $this->assertEquals("Breed not found (master breed does not exist)", $message);
    }

    public function testGetAllSubBreedsRandomSingle()
    {
        $r = $this->controller->getAllSubBreedsRandomSingle('bullterrier');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("success", $status);
        $this->assertEquals("staffordshire", $message);

        $r = $this->controller->getAllSubBreedsRandomSingle('BAD');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("error", $status);
        $this->assertEquals("Breed not found (master breed does not exist)", $message);

        $r = $this->controller->getAllSubBreedsRandomSingle('affenpinscher');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("error", $status);
        $this->assertEquals("Breed not found (no sub breeds exist for this master breed)", $message);
    }

    public function testGetAllSubBreedsRandomMulti()
    {
        $r = $this->controller->getAllSubBreedsRandomMulti('bullterrier', 2);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("success", $status);
        $this->assertEquals(["staffordshire"], $message);

        $this->setUp();
        $r = $this->controller->getAllSubBreedsRandomMulti('bullterrier', 1);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("success", $status);
        $this->assertEquals(["staffordshire"], $message);

        $this->setUp();
        $r = $this->controller->getAllSubBreedsRandomMulti('bullterrier', 9999);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("success", $status);
        $this->assertEquals(["staffordshire"], $message);

        $this->setUp();
        $r = $this->controller->getAllSubBreedsRandomMulti('bullterrier', -1);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("success", $status);
        $this->assertEquals(["staffordshire"], $message);

        $this->setUp();
        $r = $this->controller->getAllSubBreedsRandomMulti('bad', 1);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("error", $status);
        $this->assertEquals("Breed not found (master breed does not exist)", $message);

        $this->setUp();
        $r = $this->controller->getAllSubBreedsRandomMulti('DOESNOTEXIST', 1);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("error", $status);
        $this->assertEquals("Breed not found (master breed does not exist)", $message);

        $this->setUp();
        $r = $this->controller->getAllSubBreedsRandomMulti('affenpinscher', -1);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("error", $status);
        $this->assertEquals("Breed not found (no sub breeds exist for this master breed)", $message);
    }

    public function testGetTopLevelImages()
    {
        $r = $this->controller->getTopLevelImages('affenpinscher');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("success", $status);
        $this->assertEquals(["https://images.dog.ceo/breeds/affenpinscher/image.jpg"], $message);

        $r = $this->controller->getTopLevelImages('DOESNOTEXIST');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("error", $status);
        $this->assertEquals("Breed not found (master breed does not exist)", $message);

        $r = $this->controller->getTopLevelImages('bad');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("error", $status);
        $this->assertEquals("Breed not found (master breed does not exist)", $message);
    }

    public function testGetRandomTopLevelImage()
    {
        $r = $this->controller->getRandomTopLevelImage('affenpinscher');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("success", $status);
        $this->assertEquals("https://images.dog.ceo/breeds/affenpinscher/image.jpg", $message);

        $r = $this->controller->getRandomTopLevelImage('DOESNOTEXIST');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("error", $status);
        $this->assertEquals("Breed not found (master breed does not exist)", $message);

        $r = $this->controller->getRandomTopLevelImage('bad');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("error", $status);
        $this->assertEquals("Breed not found (master breed does not exist)", $message);
    }

    public function testGetRandomTopLevelImages()
    {
        $r = $this->controller->getRandomTopLevelImages('affenpinscher', 3);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("success", $status);
        $this->assertEquals(["https://images.dog.ceo/breeds/affenpinscher/image.jpg"], $message);

        $r = $this->controller->getRandomTopLevelImages('affenpinscher', 999);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("success", $status);
        $this->assertEquals(["https://images.dog.ceo/breeds/affenpinscher/image.jpg"], $message);

        $r = $this->controller->getRandomTopLevelImages('affenpinscher', -1);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("success", $status);
        $this->assertEquals(["https://images.dog.ceo/breeds/affenpinscher/image.jpg"], $message);

        $r = $this->controller->getRandomTopLevelImages('affenpinscher', "bad");
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("success", $status);
        $this->assertEquals(["https://images.dog.ceo/breeds/affenpinscher/image.jpg"], $message);

        $r = $this->controller->getRandomTopLevelImages('DOESNOTEXIST', 3);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("error", $status);
        $this->assertEquals("Breed not found (master breed does not exist)", $message);

        $r = $this->controller->getRandomTopLevelImages('bad', 3);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals("error", $status);
        $this->assertEquals("Breed not found (master breed does not exist)", $message);
    }
}