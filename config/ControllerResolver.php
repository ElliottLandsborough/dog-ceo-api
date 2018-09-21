<?php

namespace config;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolver as cResolver;

use config\RoutesMaker;

/**
 * Extenc the controller resolver so that we can provide the
 * constructor with (RouteCollection $routes, Request $request)
 */
class ControllerResolver extends cResolver
{
    private $routes;
    private $request;

    public function __construct(RoutesMaker $routesMaker)
    {
        parent::__construct();

        $this->routes = $routes;
        $this->request = $request;
    }

    /**
     * Returns an instantiated controller.
     *
     * @param string $class A class name
     *
     * @return object
     */
    protected function instantiateController($class)
    {
        return new $class($this->request, $this->routes);
    }
}
