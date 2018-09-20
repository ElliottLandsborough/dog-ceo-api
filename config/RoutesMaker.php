<?php
namespace config;

//use controllers\StatsController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use controllers\CacheController;

class routesMaker
{
    private $routes;
    private $routesArray = [];

    public function __construct()
    {
        $this->routes = new RouteCollection();
    }

    public function addRoute($path, $function, $params = [])
    {
        $this->routesArray[] = [
            'path'     => $path,
            'function' => $function,
            'params'   => $params,
        ];
    }

    private function getClassToUse()
    {
        $classToUse = 'controllers\ApiController';
        if (getenv('DOG_CEO_GATEWAY_ENABLE') && getenv('DOG_CEO_GATEWAY_ENABLE') == 'true') {
            $classToUse = 'controllers\ApiControllerGateway';
        }

        return $classToUse;
    }

    private function getRouteSlug($path)
    {
        $slug = $path;
        $slug = str_replace(['{', '}'], '', $slug);
        $slug = str_replace('/', ' ', $slug);
        $slug = trim($slug);
        $slug = ucwords($slug);
        $slug = str_replace(' ', '', $slug);
        return $slug;
    }

    private function generateRoute($route)
    {
        $classToUse = $this->getClassToUse();

        $slug = $this->getRouteSlug($route['path']);

        $params = [];

        if (isset($route['params'])) {
            $params = array_merge($params, $route['params']);
        }

        $params['_controller'] = [new $classToUse(), $route['function']];

        $this->routes->add($slug, new Route($route['path'], $params));

        // add an xml route (won't work in php development server)
        $params['xml'] = true;
        $this->routes->add($slug . 'Xml', new Route($route['path'] . '/xml', $params));

        return $this;
    }

    public function generateRoutesFromArray()
    {
        foreach ($this->routesArray as $route) {
            $this->generateRoute($route);
            // if the route is capable of alts, make another one with alts enabled
            if (isset($route['params']['alt']) && $route['params']['alt'] === false) {
                $route['path'] .= '/alt';
                $route['params']['alt'] = true;
                $this->generateRoute($route);
            }
        }

        return $this;
    }

    public function clearCacheRoute()
    {
        // clear the cache
        $this->routes->add('clearAllCacheFiles', new Route(
            '/clear-cache',
            [
                '_controller' => [
                    new CacheController(),
                    'clearAllCacheFiles'
                ]
            ]
        ));

        return $this;
    }

    public function getRoutes()
    {
        return $this->routes;
    }
}
