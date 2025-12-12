<?php

// tests/Util/CacheControllerTest.php

namespace App\Tests\Controller;

use \Psr\Container\ContainerInterface;
use App\Controller\CacheController;
use App\Util\BreedUtil;
use App\Util\MockApi;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;

class CacheControllerTest extends WebTestCase
{
    protected BreedUtil $util;
    protected CacheController $controller;

    public function setUp(): void
    {
        $this->util = new BreedUtil(new MockApi(), new FilesystemAdapter());
        $this->util->clearCache();

        $this->controller = new CacheController(
            $this->util, 
            $this->createMock(ContainerInterface::class),
            new Request()
        );
    }

    public function testCacheClearFail(): void
    {
        $r = $this->controller->cacheClear();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals('Cache was not cleared', $message);
    }

    public function testCacheClearSuccess(): void
    {
        if (!isset($_ENV['DOG_CEO_CACHE_KEY'])) {
            exit('Cache key was not set for some reason?');
        }

        $request = new Request();
        $request->headers->set('auth-key', $_ENV['DOG_CEO_CACHE_KEY']);
        $this->controller = new CacheController(
            $this->util,
            $this->createMock(ContainerInterface::class),
            $request
        );

        $r = $this->controller->cacheClear();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals('Success, cache was cleared with key', $message);
    }
}
