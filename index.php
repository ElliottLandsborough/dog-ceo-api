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
use config\RoutesMaker;

// try to load .env, silently error if it doesn't exist
//try {
    $dotenv = new Dotenv(__DIR__);
    $dotenv->load();
/*}

} catch (InvalidPathException $e) {
    // the .env file does not exist
}
*/

$routesMaker = new RoutesMaker();

$routesMaker->addRoute('/breeds/list', 'breedList');
$routesMaker->addRoute('/breeds/list/all', 'breedListAll');
$routesMaker->addRoute('/breed/{breed}/list', 'breedListSub', ['breed' => null]);
$routesMaker->addRoute('/breeds/image/random', 'breedAllRandomImage', ['alt' => false]);
$routesMaker->addRoute('/breeds/image/random/{amount}', 'breedAllRandomImages', ['alt' => false]);
$routesMaker->addRoute('/breed/{breed}/images', 'breedImage', ['breed'  => null, 'breed2' => null, 'all'  => true, 'alt' => false]);
$routesMaker->addRoute('/breed/{breed}/images/random', 'breedImage', ['breed'  => null, 'breed2' => null, 'alt' => false,]);
$routesMaker->addRoute('/breed/{breed}/images/random/{amount}', 'breedImage', ['breed'  => null, 'breed2' => null, 'alt' => false]);
$routesMaker->addRoute('/breed/{breed}/{breed2}/images', 'breedImage', ['breed'  => null, 'breed2' => null, 'all' => true, 'alt' => false,]);
$routesMaker->addRoute('/breed/{breed}/{breed2}/images/random', 'breedImage', ['breed'  => null, 'breed2' => null, 'alt' => false,]);
$routesMaker->addRoute('/breed/{breed}/{breed2}/images/random/{amount}', 'breedImage', ['breed'  => null, 'breed2' => null, 'alt' => false,]);
$routesMaker->addRoute('/breed/{breed}', 'breedText', ['breed'  => null, 'breed2' => null]);
$routesMaker->addRoute('/breed/{breed}/{breed2}', 'breedText', ['breed'  => null, 'breed2' => null]);

$routes = $routesMaker->generateRoutesFromArray()->clearCacheRoute()->getRoutes();

$request = Request::createFromGlobals();
$context = new RequestContext();
$context->fromRequest($request);
$matcher = new UrlMatcher($routes, $context);
$controllerResolver = new HttpKernel\Controller\ControllerResolver();
$argumentResolver = new HttpKernel\Controller\ArgumentResolver();

try {
    $request->attributes->add($matcher->match($request->getPathInfo()));

    $controller = $controllerResolver->getController($request);
    $arguments = $argumentResolver->getArguments($request, $controller);

    $response = call_user_func_array($controller, $arguments);
} catch (ResourceNotFoundException $e) {
    if ($request->getPathInfo() == '/') {
        header('Location: https://dog.ceo/dog-api');
        die;
    } else {
        $response = new Response('404 Error, page not found. API documentation is located at https://dog.ceo/dog-api', 404);
    }
} catch (Exception $e) {
    $error = 'Error occurred';

    if (getenv('DOG_CEO_DEBUG') && getenv('DOG_CEO_DEBUG') == 'true') {
        $error = $e->getMessage();
    }

    $response = new Response($error, 500);
}

$response->send();

// keep some stats after the response is sent
// only do stats if db exists in .env
/*
if (isset($request) && getenv('DOG_CEO_DB_HOST')) {
    //$routeName = $request->get('_route');
    $uri = $request->getRequestUri();
    // only save stats if successful request
    if ($uri !== '/stats' && $response->getStatusCode() == '200') {
        $stats = new Statistic();
        $stats->save($uri);
    }
}
*/
