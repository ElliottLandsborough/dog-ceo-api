<?php

//use controllers\StatsController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use controllers\CacheController;

$classToUse = 'controllers\ApiController';
if (getenv('DOG_CEO_GATEWAY_ENABLE') && getenv('DOG_CEO_GATEWAY_ENABLE') == 'true') {
    $classToUse = 'controllers\ApiControllerGateway';
}

// define routes
$routes = new RouteCollection();

// list all breed names
$routes->add('breedList', new Route(
    '/breeds/list',
    [
        'filename' => null,
        'raw' => false,
        '_controller' => [
            new $classToUse(),
            'breedList'
        ]
    ]
));

// list all breed names including sub breeds
$routes->add('breedListAll', new Route(
    '/breeds/list/all',
    [
        'filename' => null,
        'raw' => false,
        '_controller' => [
            new $classToUse(),
            'breedListAll'
        ]
    ]
));

// list sub breeds
$routes->add('breedSubList', new Route(
    '/breed/{breed}/list',
    [
        'breed' => null,
        '_controller' => [
            new $classToUse(),
            'breedListSub'
        ]
    ]
));

// random image from all breeds
$routes->add('breedAllRandom', new Route(
    '/breeds/image/random',
    [
        'filename' => null,
        '_controller' => [
            new $classToUse(),
            'breedAllRandomImage'
        ]
    ]
));

// random image from all breeds
$routes->add('breedAllRandomAlt', new Route(
    '/breeds/image/random/alt',
    [
        'filename' => null,
        'alt'      => true,
        '_controller' => [
            new $classToUse(),
            'breedAllRandomImage'
        ]
    ]
));

// get multiple random images of any breed
$routes->add('breedAllMultiRandom', new Route(
    '/breeds/image/random/{amount}',
    [
        'filename' => null,
        '_controller' => [
            new $classToUse(),
            'breedAllRandomImages'
        ]
    ]
));

// get multiple random images of any breed
$routes->add('breedAllMultiRandomAlt', new Route(
    '/breeds/image/random/{amount}/alt',
    [
        'filename' => null,
        'alt'      => true,
        '_controller' => [
            new $classToUse(),
            'breedAllRandomImages'
        ]
    ]
));

// get all breed images
$routes->add('breedAllImages', new Route(
    '/breed/{breed}/images',
    [
        'breed'  => null,
        'breed2' => null,
        'all'    => true,
        '_controller' => [
            new $classToUse(),
            'breedImage'
        ]
    ]
));

// get all breed images
$routes->add('breedAllImagesAlt', new Route(
    '/breed/{breed}/images/alt',
    [
        'breed'  => null,
        'breed2' => null,
        'all'    => true,
        'alt'    => true,
        '_controller' => [
            new $classToUse(),
            'breedImage'
        ]
    ]
));

// get a random image of a breed
$routes->add('breedRandomImage', new Route(
    '/breed/{breed}/images/random',
    [
        'breed'  => null,
        'breed2' => null,
        '_controller' => [
            new $classToUse(),
            'breedImage'
        ]
    ]
));

// get a random image of a breed
$routes->add('breedRandomImageAlt', new Route(
    '/breed/{breed}/images/random/alt',
    [
        'breed'  => null,
        'breed2' => null,
        'alt'    => true,
        '_controller' => [
            new $classToUse(),
            'breedImage'
        ]
    ]
));

// get multiple random images of a breed
$routes->add('breedRandomImageAmount', new Route(
    '/breed/{breed}/images/random/{amount}',
    [
        'breed'  => null,
        'breed2' => null,
        '_controller' => [
            new $classToUse(),
            'breedImage'
        ]
    ]
));

// get multiple random images of a breed
$routes->add('breedRandomImageAmountAlt', new Route(
    '/breed/{breed}/images/random/{amount}/alt',
    [
        'breed'  => null,
        'breed2' => null,
        'alt'    => true,
        '_controller' => [
            new $classToUse(),
            'breedImage'
        ]
    ]
));

// get all images from sub breed
$routes->add('breedSubAllImages', new Route(
    '/breed/{breed}/{breed2}/images',
    [
        'breed'  => null,
        'breed2' => null,
        'all'    => true,
        '_controller' => [
            new $classToUse(),
            'breedImage'
        ]
    ]
));

// get random image from sub breed
$routes->add('breedSubRandomImage', new Route(
    '/breed/{breed}/{breed2}/images/random',
    [
        'breed'  => null,
        'breed2' => null,
        '_controller' => [
            new $classToUse(),
            'breedImage'
        ]
    ]
));

// get random image from sub breed
$routes->add('breedSubRandomImageAlt', new Route(
    '/breed/{breed}/{breed2}/images/random/alt',
    [
        'breed'  => null,
        'breed2' => null,
        'alt'    => true,
        '_controller' => [
            new $classToUse(),
            'breedImage'
        ]
    ]
));

// get multiple random images from sub breed
$routes->add('breedSubRandomImageAmount', new Route(
    '/breed/{breed}/{breed2}/images/random/{amount}',
    [
        'breed'  => null,
        'breed2' => null,
        '_controller' => [
            new $classToUse(),
            'breedImage'
        ]
    ]
));

// get multiple random images from sub breed
$routes->add('breedSubRandomImageAmountAlt', new Route(
    '/breed/{breed}/{breed2}/images/random/{amount}/alt',
    [
        'breed'  => null,
        'breed2' => null,
        'alt'    => true,
        '_controller' => [
            new $classToUse(),
            'breedImage'
        ]
    ]
));

// get master breed info
$routes->add('breedText', new Route(
    '/breed/{breed}',
    [
        'breed' => null,
        'breed2' => null,
        '_controller' => [
            new $classToUse(),
            'breedText'
        ]
    ]
));

// get sub breed info
$routes->add('subBreedText', new Route(
    '/breed/{breed}/{breed2}',
    [
        'breed' => null,
        'breed2' => null,
        '_controller' => [
            new $classToUse(),
            'breedText'
        ]
    ]
));

// clear the cache
$routes->add('clearAllCacheFiles', new Route(
    '/clear-cache',
    [
        '_controller' => [
            new CacheController(),
            'clearAllCacheFiles'
        ]
    ]
));

/*
// get sub breed info
$routes->add('statsPage', new Route(
    '/stats',
    [
        '_controller' => [
            new StatsController(),
            'statsPage'
        ]
    ]
));
*/

return $routes;
