<?php

namespace controllers;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use models\Cache;

class ApiController
{
    private $imageUrl = false;
    private $breedDirs = [];
    private $cache;

    public function __construct()
    {
        $this->cache = new Cache;
        $this->breedDirs = $this->cache->storeAndReturn('returnBreedDirs', 60, function () {
            return $this->returnBreedDirs();
        });
        $this->setimageUrl();
    }

    // the domain and port and protocol
    private function baseUrl()
    {
        $actual_link = (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'];

        return $actual_link;
    }

    // set the url to the images
    private function setimageUrl()
    {
        $this->imageUrl = $this->baseUrl().'/api/img/'; // must have trailing slash for now
    }

    // return the path to the images
    private function getBreedsDirectory()
    {
        $path = realpath(__DIR__.'/../img');

        if (!$path) {
            return false;
        }

        return $path;
    }

    // get an aray of all the breed directories, set the var
    // should only be called from construct, cached
    private function returnBreedDirs()
    {
        $dir = $this->getBreedsDirectory();

        if (!$dir) {
            $dirs = [];
        } else {
            // this is super dangerous on its own, make sure to check that $dir exists
            $dirs = glob($this->getBreedsDirectory().'/*', GLOB_ONLYDIR);
        }

        return $dirs;
    }

    private function getBreedDirs()
    {
        return $this->breedDirs;
    }

    // two dimensional array of all breeds
    private function getAllBreeds()
    {
        $breeds = $this->getBreedDirs();

        $breedList = [];

        foreach ($breeds as $breed) {
            $breed = basename($breed);

            $exp = explode('-', basename($breed));

            $name = $exp[0];

            $sub = count($exp) > 1 ? $exp[1] : false;

            if (!isset($breedList[$name])) {
                $breedList[$name] = [];
            }

            if ($sub) {
                $breedList[$name][] = $sub;
            }
        }

        return $breedList;
    }

    // array of master breeds
    private function getMasterBreeds()
    {
        $allBreeds = $this->getAllBreeds();

        $masterBreeds = [];

        foreach ($allBreeds as $master => $sub) {
            $masterBreeds[] = $master;
        }

        $masterBreeds = array_unique($masterBreeds);

        sort($masterBreeds);

        return $masterBreeds;
    }

    // array of sub breeds by breed name
    private function getSubBreeds($breed = null)
    {
        $allBreeds = $this->getAllBreeds();

        foreach ($allBreeds as $master => $sub) {
            if (strtolower($breed) == $master) {
                return $sub;
            }
        }

        return $false;
    }

    // json response of 2d breeds array
    public function breedListAll()
    {
        $responseArray = (object) ['status' => 'error', 'code' => '404', 'message' => 'No breeds found'];

        $allBreeds = $this->getAllBreeds();

        if (count($allBreeds)) {
            $responseArray = (object) ['status' => 'success', 'message' => $allBreeds];
        }

        $response = new JsonResponse($responseArray);

        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }

    // json response of master breeds
    public function breedList()
    {
        $responseArray = (object) ['status' => 'success', 'message' => $this->getMasterBreeds()];

        $response = new JsonResponse($responseArray);

        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }

    // json response of sub breeds
    public function breedListSub($breed = null)
    {
        $responseArray = (object) ['status' => 'error', 'code' => '404', 'message' => 'Breed not found'];

        $breedSubList = $this->getSubBreeds($breed);

        if (is_array($breedSubList)) {
            $responseArray = (object) ['status' => 'success', 'message' => $breedSubList];
        }

        $response = new JsonResponse($responseArray);

        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }

    // clean up a breed subdirectory name
    private function cleanBreedSubDir($string)
    {
        // convert spaniel-cocker
        $exp = explode('-', $string);
        // to spaniel/cocker
        return $exp[0].(count($exp) > 1 ? '/'.$exp[1] : null);
    }

    // see if a string matches a directory
    private function matchBreedString($string = null, $string2 = null)
    {
        $breedDirs = $this->getBreedDirs();

        foreach ($breedDirs as $dir) {
            // single breed e.g /api/breed/basset
            if (strtolower($string) == $this->cleanBreedSubDir(basename($dir))) {
                return $dir;
            }

            // sub breed e.g /api/breed/hound/afghan
            $exp = explode('/', $this->cleanBreedSubDir(basename($dir)));
            if ($exp == [$string, $string2]) {
                return $dir;
            }

            // perhaps a multiple directory match?
            if (!isset($multi)) {
                $multi = [];
            }
            if ($exp[0] == $string) {
                $multi[] = $dir;
            }
        }

        // return multi dir if larger than 0
        if (count($multi)) {
            return $multi;
        }

        return false;
    }

    // get all images from the specified directory
    private function getAllImages($imagesDir)
    {
        $images = $this->breedDirs = $this->cache->storeAndReturn('getAllImages.'.md5(serialize($imagesDir)), 60, function () use ($imagesDir) {
            if (is_array($imagesDir) && count($imagesDir)) {
                // match multi breeds
                $images = [];
                foreach ($imagesDir as $iDir) {
                    $images = array_merge($images, glob($iDir.'/*.{jpg,jpeg,png,gif}', GLOB_BRACE));
                }
            } else {
                // match single breed
                $images = glob($imagesDir.'/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
            }

            return $images;
        });

        return $images;
    }

    // get a random image from the specified directory
    private function getRandomImage($imagesDir)
    {
        $images = $this->getAllImages($imagesDir);

        return $images[array_rand($images)];
    }

    // return an image based on the $breed string passed
    public function breedImage($breed = null, $breed2 = null, $all = false)
    {
        // default response, 404
        $responseArray = (object) ['status' => 'error', 'code' => '404', 'message' => 'Breed not found'];

        $match = $this->matchBreedString($breed, $breed2);
        if ($match) {
            // return all images?
            if ($all) {
                $images = $this->getAllImages($match);
                foreach ($images as $key => $image) {
                    $explodedPath = explode('/', $image);
                    $directory = $explodedPath[count($explodedPath) - 2];
                    $images[$key] = $this->imageUrl.$directory.'/'.basename($image);
                }
                $responseArray = (object) ['status' => 'success', 'message' => $images];
            } else {
                // otherwise, we just want one image
                $image = $this->getRandomImage($match);
                $explodedPath = explode('/', $image);
                $directory = $explodedPath[count($explodedPath) - 2];
                // json response with url to image
                if ($image) {
                    $responseArray = (object) ['status' => 'success', 'message' => $this->imageUrl.$directory.'/'.basename($image)];
                }
            }
        }

        $response = new JsonResponse($responseArray);

        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }

    // return a random image of any breed
    public function breedAllRandomImage()
    {
        // pick a random dir
        $randomBreedDir = $this->getBreedDirs()[array_rand($this->getBreedDirs())];

        // pick a random image from that dir
        $file = $this->getRandomImage($randomBreedDir);

        $exp = explode('/', $file);

        $responseArray = (object) ['status' => 'success', 'message' => $this->imageUrl.$exp[count($exp) - 2].'/'.basename($file)];

        $response = new JsonResponse($responseArray);

        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }

    // make sure the yaml file exists
    private function breedYamlFile($breed = null, $breed2 = null)
    {
        // only keep lower case alphabetical
        $breed = strlen($breed) ? strtolower(preg_replace('/[^A-Za-z0-9]/', '', $breed)) : false;
        $breed2 = strlen($breed2) ? strtolower(preg_replace('/[^A-Za-z0-9]/', '', $breed2)) : false;

        // generate a sensible file name
        if ($breed) {
            $fileName = $breed;

            if ($breed2) {
                $fileName .= '-'.$breed2;
            }
        }

        if (isset($fileName)) {
            $path = __DIR__.'/../content/breed-info/'.$fileName.'.yaml';

            return realpath($path);
        }

        return false;
    }

    /**
     * Returns only array entries listed in a whitelist.
     *
     * @param array $array     original array to operate on
     * @param array $whitelist keys you want to keep
     *
     * @return array
     */
    private function arrayWhitelist($array, $whitelist)
    {
        return array_intersect_key(
            $array,
            array_flip($whitelist)
        );
    }

    // get the breed text from the yaml file
    private function getBreedText($breed = null, $breed2 = null)
    {
        $whitelist = ['name', 'info'];

        $path = $this->breedYamlFile($breed, $breed2);

        if ($path) {
            $array = yaml_parse_file($path);

            return $this->arrayWhitelist($array, $whitelist);
        }

        return false;
    }

    // super simple dev cms
    // add yaml files to /content/breed-info
    // e.g spaniel.yaml
    //     spaniel-cocker.yaml
    public function breedText($breed = null, $breed2 = null)
    {
        // default response, 404
        $responseArray = (object) ['status' => 'error', 'code' => '404', 'message' => 'No breed info available.'];

        $content = $this->getBreedText($breed, $breed2);

        if ($content !== false) {
            $responseArray = (object) ['status' => 'success', 'message' => $content];
        }

        $response = new JsonResponse($responseArray);

        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }
}
