<?php

// tests/Util/CacheControllerTest.php

namespace App\Tests\Controller;

use App\Controller\CacheController;
use App\Util\BreedUtil;
use App\Util\MockApi;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CacheControllerTest extends WebTestCase
{
    protected BreedUtil $util;
    protected CacheController $controller;

    public function setUp(): void
    {
        $this->util = new BreedUtil(new MockApi(), new FilesystemAdapter());
        $this->util->clearCache();

        // Create a proper container mock with request stack
        $container = $this->createMock(ContainerInterface::class);
        $requestStack = new RequestStack();
        $requestStack->push(new Request());

        $container->method('get')
            ->willReturnMap([
                ['request_stack', $requestStack],
            ]);

        $this->controller = new CacheController(
            $this->util,
            $container,
            new Request(),
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
        $request = new Request();
        $request->headers->set('auth-key', 'test-key');

        // Create a proper container mock with request stack that has the auth request
        $container = $this->createMock(ContainerInterface::class);
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $container->method('get')
            ->willReturnMap([
                ['request_stack', $requestStack],
            ]);

        $this->controller = new CacheController(
            $this->util,
            $container,
            $request,
        );

        $r = $this->controller->cacheClear();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals('Success, cache was cleared', $message);
    }

    public function testSanitizeKeyValidInput(): void
    {
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('sanitizeKey');
        $method->setAccessible(true);

        // Valid key with allowed characters
        $result = $method->invoke($this->controller, 'validKey123-_.');
        $this->assertEquals('validKey123-_.', $result);

        // Valid key with special characters
        $result = $method->invoke($this->controller, 'test!@#$%^&*()+=');
        $this->assertEquals('test!@#$%^&*()+=', $result);
    }

    public function testSanitizeKeyInvalidInput(): void
    {
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('sanitizeKey');
        $method->setAccessible(true);

        // Null input
        $result = $method->invoke($this->controller, null);
        $this->assertNull($result);

        // Empty string
        $result = $method->invoke($this->controller, '');
        $this->assertNull($result);

        // Whitespace only
        $result = $method->invoke($this->controller, '   ');
        $this->assertNull($result);

        // Too short after sanitization
        $result = $method->invoke($this->controller, 'short');
        $this->assertNull($result);
    }

    public function testSanitizeKeyRemovesInvalidCharacters(): void
    {
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('sanitizeKey');
        $method->setAccessible(true);

        // Input with invalid characters that should be removed
        $result = $method->invoke($this->controller, 'test<script>alert();</script>validkey123');
        $this->assertEquals('testscriptalert()scriptvalidkey123', $result);

        // Input with newlines and tabs
        $result = $method->invoke($this->controller, "test\n\t\rvalidkey123");
        $this->assertEquals('testvalidkey123', $result);

        // Input with SQL injection attempt
        $result = $method->invoke($this->controller, "test'; DROP TABLE users; --validkey123");
        $this->assertEquals('testDROPTABLEusers--validkey123', $result);

        // Input with no valid characters
        $result = $method->invoke($this->controller, "<>/?\\|{}[]~`");
        $this->assertNull($result);
    }

    public function testSanitizeKeyLengthLimits(): void
    {
        // Use reflection to access the private method
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('sanitizeKey');
        $method->setAccessible(true);

        // Test length truncation at 128 characters
        $longKey = str_repeat('a', 150).'12345678';
        $result = $method->invoke($this->controller, $longKey);
        $this->assertEquals(128, strlen($result));
        $this->assertEquals(str_repeat('a', 128), $result);

        // Test exactly 128 characters
        $exactKey = str_repeat('a', 120).'12345678';
        $result = $method->invoke($this->controller, $exactKey);
        $this->assertEquals($exactKey, $result);

        // Test minimum length requirement (8 characters)
        $result = $method->invoke($this->controller, '12345678');
        $this->assertEquals('12345678', $result);

        // Test below minimum length requirement (8 characters)
        $result = $method->invoke($this->controller, '1234567');
        $this->assertNull($result);
    }

    public function testSanitizeKeyWhitespaceHandling(): void
    {
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('sanitizeKey');
        $method->setAccessible(true);

        // Leading and trailing whitespace should be trimmed
        $result = $method->invoke($this->controller, '  validkey123  ');
        $this->assertEquals('validkey123', $result);

        // Multiple spaces in middle should be removed (not allowed character)
        $result = $method->invoke($this->controller, 'valid key 123 test');
        $this->assertEquals('validkey123test', $result);
    }

    public function testGetCacheKeyFromEnvWithValidKey(): void
    {
        // Set environment variable
        $_ENV['DOG_CEO_CACHE_KEY'] = 'test-key-12345678';

        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('getCacheKeyFromEnv');
        $method->setAccessible(true);

        $result = $method->invoke($this->controller);
        $this->assertEquals('test-key-12345678', $result);

        // Clean up
        unset($_ENV['DOG_CEO_CACHE_KEY']);
    }

    public function testGetCacheKeyFromEnvWithLongKey(): void
    {
        // Set environment variable longer than 128 chars
        $longKey = str_repeat('a', 150);
        $_ENV['DOG_CEO_CACHE_KEY'] = $longKey;

        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('getCacheKeyFromEnv');
        $method->setAccessible(true);

        $result = $method->invoke($this->controller);
        $this->assertEquals(128, strlen($result));
        $this->assertEquals(str_repeat('a', 128), $result);

        // Clean up
        unset($_ENV['DOG_CEO_CACHE_KEY']);
    }

    public function testGetCacheKeyFromEnvWithMissingKey(): void
    {
        // Ensure environment variable is not set
        unset($_ENV['DOG_CEO_CACHE_KEY']);

        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('getCacheKeyFromEnv');
        $method->setAccessible(true);

        $result = $method->invoke($this->controller);
        $this->assertEquals('', $result);
    }

    public function testGetCacheKeyFromEnvWithEmptyKey(): void
    {
        // Set empty environment variable
        $_ENV['DOG_CEO_CACHE_KEY'] = '';

        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('getCacheKeyFromEnv');
        $method->setAccessible(true);

        $result = $method->invoke($this->controller);
        $this->assertEquals('', $result);

        // Clean up
        unset($_ENV['DOG_CEO_CACHE_KEY']);
    }

    public function testGetCacheKeyFromEnvExactly128Chars(): void
    {
        // Set environment variable exactly 128 chars
        $exactKey = str_repeat('a', 128);
        $_ENV['DOG_CEO_CACHE_KEY'] = $exactKey;

        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('getCacheKeyFromEnv');
        $method->setAccessible(true);

        $result = $method->invoke($this->controller);
        $this->assertEquals(128, strlen($result));
        $this->assertEquals($exactKey, $result);

        // Clean up
        unset($_ENV['DOG_CEO_CACHE_KEY']);
    }
}
