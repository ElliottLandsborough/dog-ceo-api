<?php

namespace controllers\tests\overrides;

use controllers\ApiController;

class ApiControllerOverride extends ApiController
{
    public function __construct()
    {
        parent::__construct();
    }

    // get an aray of all the breed directories, set the var
    // should only be called from construct, cached
    public function returnBreedDirs()
    {
        return [
            '/directory/that/exists/or/not/spaniel-cocker',
            '/directory/that/exists/or/not/spaniel-irish'
        ];

        return $array;
    }

    // get all images from the specified directory
    public function getAllImages($imagesDir)
    {
        $images = [
            '/directory/that/exists/or/not/spaniel-cocker/n02102973_1037.jpg',
            '/directory/that/exists/or/not/spaniel-cocker/n02102973_1066.jpg',
            '/directory/that/exists/or/not/spaniel-irish/n02102973_1066.jpg',
        ];

        return $images;
    }
}
