<?php

// tests/Util/ExceptionListenerTest.php

namespace App\Tests\Util;

use App\EventListener\ExceptionListener;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ExceptionListenerTest extends TestCase
{
    protected $exceptionListener;

    // runs per test
    public function setUp(): void
    {
        $this->exceptionListener = new ExceptionListener();
    }

    public function testOnKernelException()
    {
        $expected = (object) [
            'status'  => 'error',
            'message' => 'Error Message Example with code: 0',
            'code'    => 500,
        ];

        $request = new Request();
        $exception = new Exception('Error Message Example');
        $event = new GetResponseForExceptionEvent(new TestKernel(), $request, HttpKernelInterface::MAIN_REQUEST, $exception);
        $this->exceptionListener->onKernelException($event);
        $response = $event->getResponse();
        $json = $response->getContent();
        $object = json_decode($json);

        $this->assertEquals($expected, $object);
    }
}

class TestKernel implements HttpKernelInterface
{
    public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
    {
        return new Response('unused body text');
    }
}
