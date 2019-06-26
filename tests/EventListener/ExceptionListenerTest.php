<?php

// tests/Util/ExceptionListenerTest.php

namespace App\Tests\Util;

use App\EventListener\ExceptionListener;
use Symfony\Component\HttpFoundation\Request;
use \Exception;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use PHPUnit\Framework\TestCase;

class ExceptionListenerTest extends TestCase
{
    protected $exceptionListener;

    // runs per test
    public function setUp(): void
    {
        $this->exceptionListener = new ExceptionListener;
    }

    public function testOnKernelException()
    {
        $expected = (object) [
            'status' => 'error',
            'message' => 'Error Message Example with code: 0',
            'code'  => 500,
        ];

        $request = new Request();
        $exception = new Exception('Error Message Example');
        $event = new GetResponseForExceptionEvent(new TestKernel(), $request, HttpKernelInterface::MASTER_REQUEST, $exception);
        $this->exceptionListener->onKernelException($event);
        $response = $event->getResponse();
        $json = $response->getContent();
        $object = json_decode($json);

        $this->assertEquals($expected, $object);
        //print_r($event->getException());
    }
}

class TestKernel implements HttpKernelInterface
{
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        return new Response('unused body text');
    }
}