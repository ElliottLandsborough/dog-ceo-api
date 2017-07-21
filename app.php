<?php


use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiController
{
    private $imageUrl = false;
    private $breedDirs = [];

    public function __construct()
    {
        $this->setBreedDirs();
        $this->setimageUrl();
    }

    // the domain and port and protocol
    private function baseUrl()
    {
        $actual_link = (isset($_SERVER['HTTPS']) ? 'https' : 'http')."://$_SERVER[HTTP_HOST]";

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
        $path = realpath(__DIR__.'/img');

        if (!$path) {
            die('Error: Bad images directory.');
        }

        return $path;
    }

    // get an aray of all the breed directories, set the var
    private function setBreedDirs()
    {
        $dirs = glob($this->getBreedsDirectory().'/*', GLOB_ONLYDIR);

        $this->breedDirs = $dirs;
    }

    // two dimensional array of all breeds
    private function getAllBreeds()
    {
        $breeds = $this->breedDirs;

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

    public function breedListAll()
    {
        $responseArray = (object) ['status' => 'error', 'code' => '404', 'message' => 'No breeds found'];

        $allBreeds = $this->getAllBreeds();

        if (count($allBreeds)) {
            $responseArray = (object) ['status' => 'success', 'message' => $allBreeds];

            $response = new JsonResponse($responseArray);

            $response->headers->set('Access-Control-Allow-Origin', '*');
        }

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
        $breedDirs = $this->breedDirs;

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
        if (is_array($imagesDir) && count($imagesDir)) {
            $images = [];
            foreach ($imagesDir as $iDir) {
                $images = array_merge($images, glob($iDir.'/*.{jpg,jpeg,png,gif}', GLOB_BRACE));
            }
        } else {
            $images = glob($imagesDir.'/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
        }

        return $images;
    }

    // get a random image from the specified directory
    private function getRandomImage($imagesDir)
    {
        $images = $this->getAllImages($imagesDir);

        return $images[array_rand($images)];
    }

    // return an image based on the $breed string passed
    public function breedImage($breed = null, $breed2 = null, $raw = false, $all = false)
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

    public function breedAllRandomImage()
    {
        $randomBreedDir = $this->breedDirs[array_rand($this->breedDirs)];

        $file = $this->getRandomImage($randomBreedDir);

        $exp = explode('/', $file);

        $responseArray = (object) ['status' => 'success', 'message' => $this->imageUrl.$exp[count($exp) - 2].'/'.basename($file)];

        $response = new JsonResponse($responseArray);

        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }
}

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
