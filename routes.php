<?php

//use controllers\StatsController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use controllers\CacheController;

$classToUse = 'controllers\ApiController';
if (getenv('DOG_CEO_GATEWAY_ENABLE') && getenv('DOG_CEO_GATEWAY_ENABLE') == 'true') {
    $classToUse = 'controllers\ApiControllerGateway';
}

// path, function, params (todo: somehow make this more readable, move into class)
$routesArray = [
    ['path' => '/breeds/list',                                      'function' => 'breedList'],
    ['path' => '/breeds/list/all',                                  'function' => 'breedListAll'],
    ['path' => '/breed/{breed}/list',                               'function' => 'breedListSub',           'params' => [
                                                                                                                            'breed' => null
                                                                                                                        ]],
    ['path' => '/breeds/image/random',                              'function' => 'breedAllRandomImage',    'params' => [
                                                                                                                            'alt' => false
                                                                                                                        ]],
    ['path' => '/breeds/image/random/{amount}',                     'function' => 'breedAllRandomImages',   'params' => [
                                                                                                                            'alt' => false
                                                                                                                        ]],
    ['path' => '/breed/{breed}/images',                             'function' => 'breedImage',             'params' => [
                                                                                                                            'breed'  => null, 
                                                                                                                            'breed2' => null, 
                                                                                                                            'all'    => true,
                                                                                                                            'alt'    => false,
                                                                                                                        ]],
    ['path' => '/breed/{breed}/images/random',                      'function' => 'breedImage',                             'params' => [
                                                                                                                            'breed'  => null, 
                                                                                                                            'breed2' => null, 
                                                                                                                            'alt'    => false,
                                                                                                                        ]],
    ['path' => '/breed/{breed}/images/random/{amount}',             'function' => 'breedImage',                             'params' => [
                                                                                                                            'breed'  => null, 
                                                                                                                            'breed2' => null, 
                                                                                                                            'alt'    => false,
                                                                                                                        ]],
    ['path' => '/breed/{breed}/{breed2}/images',                    'function' => 'breedImage',             'params' => [
                                                                                                                            'breed'  => null,
                                                                                                                            'breed2' => null,
                                                                                                                            'all'    => true,
                                                                                                                            'alt'    => false,
                                                                                                                        ]],
    ['path' => '/breed/{breed}/{breed2}/images/random',             'function' => 'breedImage',             'params' => [
                                                                                                                            'breed'  => null, 
                                                                                                                            'breed2' => null, 
                                                                                                                            'alt'    => false,
                                                                                                                        ]],
    ['path' => '/breed/{breed}/{breed2}/images/random/{amount}',    'function' => 'breedImage',             'params' => [
                                                                                                                            'breed'  => null, 
                                                                                                                            'breed2' => null, 
                                                                                                                            'alt'    => false,
                                                                                                                        ]],
    ['path' => '/breed/{breed}',                                    'function' => 'breedText',             'params' => [
                                                                                                                            'breed'  => null, 
                                                                                                                            'breed2' => null, 
                                                                                                                        ]],
    ['path' => '/breed/{breed}/{breed2}',                           'function' => 'breedText',             'params' => [
                                                                                                                            'breed'  => null, 
                                                                                                                            'breed2' => null, 
                                                                                                                        ]],

];

// define routes
$routes = new RouteCollection();

// todo, move these somewhere less stupid
function getRouteSlug($path) {
    $slug = $path;
    $slug = str_replace(['{', '}'], '', $slug);
    $slug = str_replace('/', ' ', $slug);
    $slug = trim($slug);
    $slug = ucwords($slug);
    $slug = str_replace(' ', '', $slug);
    return $slug;
}

function generateRoute($route) {
    global $routes;
    global $classToUse;

    $slug = getRouteSlug($route['path']);

    $params = [];

    if (isset($route['params'])) {
        $params = array_merge($params, $route['params']);
    }

    $params['_controller'] = [new $classToUse(), $route['function']];

    $routes->add($slug, new Route($route['path'], $params));

    // add an xml route (won't work in php development server)
    $params['xml'] = true;
    $routes->add($slug . 'Xml', new Route($route['path'] . '.xml', $params));
}

foreach ($routesArray as $route) {
    generateRoute($route);
    // if the route is capable of alts, make another one with alts enabled
    if (isset($route['params']['alt']) && $route['params']['alt'] === false) {
        $route['path'] .= '/alt';
        $route['params']['alt'] = true;
        generateRoute($route);
    }
}

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
