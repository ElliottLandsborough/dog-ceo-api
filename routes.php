<?php

//use controllers\StatsController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use controllers\CacheController;

$classToUse = 'controllers\ApiController';
if (getenv('DOG_CEO_GATEWAY_ENABLE')) {
    $classToUse = 'controllers\ApiControllerGateway';
}

// define routes
$routes = new RouteCollection();

// list all breed names
$routes->add('breedList', new Route(
    '/breeds/list',
    array('filename' => null, 'raw' => false, '_controller' => array(new $classToUse(), 'breedList'))
));

// list all breed names including sub breeds
$routes->add('breedListAll', new Route(
    '/breeds/list/all',
    array('filename' => null, 'raw' => false, '_controller' => array(new $classToUse(), 'breedListAll'))
));

// list sub breeds
$routes->add('breedSubList', new Route(
    '/breed/{breed}/list',
    array('breed' => null, '_controller' => array(new $classToUse(), 'breedListSub'))
));

// random image from all breeds
$routes->add('breedAllRandom', new Route(
    '/breeds/image/random',
    array('filename' => null, '_controller' => array(new $classToUse(), 'breedAllRandomImage'))
));

// get multiple random images of any breed
$routes->add('breedAllMultiRandom', new Route(
    '/breeds/image/random/{amount}',
    array('filename' => null, '_controller' => array(new $classToUse(), 'breedAllRandomImages'))
));

// get all breed images
$routes->add('breedAllImages', new Route(
    '/breed/{breed}/images',
    array('breed' => null, 'breed2' => null, 'all' => true, '_controller' => array(new $classToUse(), 'breedImage'))
));

// get a random image of a breed
$routes->add('breedRandomImage', new Route(
    '/breed/{breed}/images/random',
    array('breed' => null, 'breed2' => null, '_controller' => array(new $classToUse(), 'breedImage'))
));

// get all images from sub breed
$routes->add('breedSubAllImages', new Route(
    '/breed/{breed}/{breed2}/images',
    array('breed' => null, 'breed2' => null, 'all' => true, '_controller' => array(new $classToUse(), 'breedImage'))
));

// get random image from sub breed
$routes->add('breedSubRandomImage', new Route(
    '/breed/{breed}/{breed2}/images/random',
    array('breed' => null, 'breed2' => null, '_controller' => array(new $classToUse(), 'breedImage'))
));

// get master breed info
$routes->add('breedText', new Route(
    '/breed/{breed}',
    array('breed' => null, 'breed2' => null, '_controller' => array(new $classToUse(), 'breedText'))
));

// get sub breed info
$routes->add('subBreedText', new Route(
    '/breed/{breed}/{breed2}',
    array('breed' => null, 'breed2' => null, '_controller' => array(new $classToUse(), 'breedText'))
));

// clear the cache
$routes->add('clearAllCacheFiles', new Route(
    '/clear-cache',
    array('_controller' => array(new CacheController(), 'clearAllCacheFiles'))
));

/*
// get sub breed info
$routes->add('statsPage', new Route(
    '/stats',
    array('_controller' => array(new StatsController(), 'statsPage'))
));
*/

return $routes;
