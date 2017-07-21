<?php

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

// define routes
$routes = new RouteCollection();

// list all breed names
$routes->add('breedList', new Route(
    '/breeds/list',
    array('filename' => null, 'raw' => false, '_controller' => 'ApiController::breedList'))
);

// list all breed names including sub breeds
$routes->add('breedListAll', new Route(
    '/breeds/list/all',
    array('filename' => null, 'raw' => false, '_controller' => 'ApiController::breedListAll'))
);

// list sub breeds
$routes->add('breedSubList', new Route(
    '/breed/{breed}/list',
    array('breed' => null, '_controller' => 'ApiController::breedListSub'))
);

// random image from all breeds
$routes->add('breedAllRandom', new Route(
    '/breeds/image/random',
    array('filename' => null, '_controller' => 'ApiController::breedAllRandomImage'))
);

// get all breed images
$routes->add('breedText', new Route(
    '/breed/{breed}',
    array('breed' => null, 'breed2' => null, '_controller' => 'ApiController::breedText'))
);

// get all breed images
$routes->add('subBreedText', new Route(
    '/breed/{breed}/{breed2}',
    array('breed' => null, 'breed2' => null, '_controller' => 'ApiController::breedText'))
);

// get all breed images
$routes->add('breedAllImages', new Route(
    '/breed/{breed}/images',
    array('breed' => null, 'breed2' => null, 'all' => true, '_controller' => 'ApiController::breedImage'))
);

// get a random image of a breed
$routes->add('breedRandomImage', new Route(
    '/breed/{breed}/images/random',
    array('breed' => null, 'breed2' => null, '_controller' => 'ApiController::breedImage'))
);

$routes->add('breedSubAllImages', new Route(
    '/breed/{breed}/{breed2}/images',
    array('breed' => null, 'breed2' => null, 'all' => true, '_controller' => 'ApiController::breedImage'))
);

$routes->add('breedSubRandomImage', new Route(
    '/breed/{breed}/{breed2}/images/random',
    array('breed' => null, 'breed2' => null, '_controller' => 'ApiController::breedImage'))
);

return $routes;
