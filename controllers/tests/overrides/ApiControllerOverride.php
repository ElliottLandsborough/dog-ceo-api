<?php

namespace controllers\tests\overrides;

use config\RoutesMaker;
use controllers\ApiController;

/**
 * Some overrides to make the tests work
 * See controllers\ApiController for function definitions.
 **/
class ApiControllerOverride extends ApiController
{
    public function __construct(RoutesMaker $routesMaker)
    {
        parent::__construct($routesMaker);
    }

    public function getBreedsDirectory()
    {
        return '/directory/that/exists/or/not';
    }

    public function returnBreedDirs()
    {
        $array = [
            '/directory/that/exists/or/not/spaniel-cocker',
            '/directory/that/exists/or/not/spaniel-irish',
        ];

        return $array;
    }

    public function getAllImages($imagesDir)
    {
        $array = [
            '/directory/that/exists/or/not/spaniel-cocker/n02102973_1037.jpg',
            '/directory/that/exists/or/not/spaniel-cocker/n02102973_1066.jpg',
            '/directory/that/exists/or/not/spaniel-irish/n02102973_1066.jpg',
        ];

        return $array;
    }
}
