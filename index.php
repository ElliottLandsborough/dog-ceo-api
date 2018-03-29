<?php

// Turn off all error reporting
// See php console or error log to fix issues
//error_reporting(0);
@ini_set('display_errors', 0);

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

// try to load .env, silently error if it doesn't exist
//try {
    $dotenv = new Dotenv(__DIR__);
    $dotenv->load();
/*}

} catch (InvalidPathException $e) {
    // the .env file does not exist
}
*/

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
// only do stats if db exists in .env
if (isset($request) && getenv('DOG_CEO_DB_HOST')) {
    //$routeName = $request->get('_route');
    $uri = $request->getRequestUri();
    // only save stats if successful request
    if ($uri !== '/stats' && $response->getStatusCode() == '200') {
        $stats = new Statistic();
        $stats->save($uri);
    }
}
