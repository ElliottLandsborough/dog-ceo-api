<?php

use controllers\ApiController;
use controllers\StatsController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

// define routes
$routes = new RouteCollection();

// list all breed names
$routes->add('breedList', new Route(
    '/breeds/list',
    array('filename' => null, 'raw' => false, '_controller' => array(new Apicontroller(), 'breedList'))
));

// list all breed names including sub breeds
$routes->add('breedListAll', new Route(
    '/breeds/list/all',
    array('filename' => null, 'raw' => false, '_controller' => array(new Apicontroller(), 'breedListAll'))
));

// list sub breeds
$routes->add('breedSubList', new Route(
    '/breed/{breed}/list',
    array('breed' => null, '_controller' => array(new Apicontroller(), 'breedListSub'))
));

// random image from all breeds
$routes->add('breedAllRandom', new Route(
    '/breeds/image/random',
    array('filename' => null, '_controller' => array(new Apicontroller(), 'breedAllRandomImage'))
));

// get all breed images
$routes->add('breedAllImages', new Route(
    '/breed/{breed}/images',
    array('breed' => null, 'breed2' => null, 'all' => true, '_controller' => array(new Apicontroller(), 'breedImage'))
));

// get a random image of a breed
$routes->add('breedRandomImage', new Route(
    '/breed/{breed}/images/random',
    array('breed' => null, 'breed2' => null, '_controller' => array(new Apicontroller(), 'breedImage'))
));

// get all images from sub breed
$routes->add('breedSubAllImages', new Route(
    '/breed/{breed}/{breed2}/images',
    array('breed' => null, 'breed2' => null, 'all' => true, '_controller' => array(new Apicontroller(), 'breedImage'))
));

// get random image from sub breed
$routes->add('breedSubRandomImage', new Route(
    '/breed/{breed}/{breed2}/images/random',
    array('breed' => null, 'breed2' => null, '_controller' => array(new Apicontroller(), 'breedImage'))
));

// get master breed info
$routes->add('breedText', new Route(
    '/breed/{breed}',
    array('breed' => null, 'breed2' => null, '_controller' => array(new Apicontroller(), 'breedText'))
));

// get sub breed info
$routes->add('subBreedText', new Route(
    '/breed/{breed}/{breed2}',
    array('breed' => null, 'breed2' => null, '_controller' => array(new Apicontroller(), 'breedText'))
));

// get sub breed info
$routes->add('statsPage', new Route(
    '/stats',
    array('_controller' => array(new StatsController(), 'statsPage'))
));

return $routes;
