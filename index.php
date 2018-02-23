<?php

// Turn off all error reporting
// See php console or error log to fix issues
error_reporting(0);

require_once realpath(__DIR__.'/vendor/autoload.php');

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\HttpKernel;
use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use models\Statistic;

$request = Request::createFromGlobals();
$routes = include realpath(__DIR__.'/routes.php');

$context = new RequestContext();
$context->fromRequest($request);
$matcher = new UrlMatcher($routes, $context);
$resolver = new HttpKernel\Controller\ControllerResolver();

try {
    $request->attributes->add($matcher->match($request->getPathInfo()));

    $controller = $resolver->getController($request);
    $arguments = $resolver->getArguments($request, $controller);

    $response = call_user_func_array($controller, $arguments);
} catch (ResourceNotFoundException $e) {
    $response = new Response('Not found', 404);
} catch (Exception $e) {
    $response = new Response('Error occurred', 500);
}

$response->send();

// keep some stats after the response is sent
try {
    $dotenv = new Dotenv(__DIR__);
    $dotenv->load();
    if (isset($request)) {
        $routeName = $request->get('_route');
        $stats = new Statistic();
        $stats->save($request->getRequestUri());
    }
} catch (InvalidPathException $e) {
    // the .env file does not exist
}
