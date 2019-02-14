<?php

namespace config;

use Symfony\Component\HttpKernel\Controller\ControllerResolver as cResolver;

/**
 * Extenc the controller resolver so that we can provide the
 * constructor with (RouteCollection $routes, Request $request).
 */
class ControllerResolver extends cResolver
{
    private $routesMaker;

    public function __construct(RoutesMaker $routesMaker)
    {
        parent::__construct();

        $this->routesMaker = $routesMaker;
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
        return new $class($this->routesMaker);
    }
}
