<?php

// tests/Util/DefaultControllerTest.php

namespace App\Tests\Util;

use App\Util\MockApi;
use App\Util\BreedUtil;
use App\Controller\DefaultController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        $this->assertCount($n, (array) $message);

        // 2 will return 2
        $n = 2;
        $r = $this->controller->getAllBreedsRandomMultiple($n);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $this->assertCount($n, (array) $message);

        // 3 will return all which is 2
        $n = 3;
        $r = $this->controller->getAllBreedsRandomMultiple($n);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $this->assertCount(2, (array) $message);

        // -1 will return 10 (so all which is 2)
        $n = -1;
        $r = $this->controller->getAllBreedsRandomMultiple($n);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $this->assertCount(2, (array) $message);

        // (string) will return 1
        $n = "$$";
        $r = $this->controller->getAllBreedsRandomMultiple($n);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $this->assertCount(1, (array) $message);
    }

    public function testGetAllTopLevelBreeds()
    {
        $r = $this->controller->getAllTopLevelBreeds();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $this->assertEquals(['affenpinscher', 'bullterrier'], $message);
    }
}