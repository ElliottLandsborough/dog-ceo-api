<?php

// tests/EventListener/ExceptionListenerTest.php

namespace App\Tests\EventListener;

use App\EventListener\ExceptionListener;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ExceptionListenerTest extends TestCase
{
    protected ExceptionListener $exceptionListener;

    // runs per test
    public function setUp(): void
    {
        $this->exceptionListener = new ExceptionListener();
    }

    public function testOnKernelException(): void
    {
        $expected = (object) [
            'status'  => 'error',
            'message' => 'Error Message Example with code: 0',
            'code'    => 500,
        ];

        $request = new Request();
        $exception = new Exception('Error Message Example');
        $event = new ExceptionEvent(new TestKernel(), $request, HttpKernelInterface::MAIN_REQUEST, $exception);
        $this->exceptionListener->onKernelException($event);
        $response = $event->getResponse();
        $json = $response->getContent();
        $object = json_decode($json);

        $this->assertEquals($expected, $object);
    }
}
