<?php

namespace controllers;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Spatie\ArrayToXml\ArrayToXml;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use models\Cache;
use config\RoutesMaker;

class ApiController
{
    private $imageUrl = false;
    private $breedDirs = [];
    private $cache;
    private $imagePath = false;

    protected $routesMaker;
    protected $alt = false;
    protected $xml = false;
    protected $type = '';

    public function __construct(RoutesMaker $routesMaker)
    {
        $this->routesMaker = $routesMaker;

        $this->imagePath = $this->imagePath();

        $this->cache = new Cache();
        if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] == 'dog.ceo') {
            $this->breedDirs = $this->cache->storeAndReturn('returnBreedDirs', 60, function () {
                return $this->returnBreedDirs();
            });
        } else {
            $this->breedDirs = $this->returnBreedDirs();
        }
        $this->setimageUrl();
        $this->setRoutes();
    }

    public function setAlt(bool $alt = false)
    {
        $this->alt = $alt;
    }

    public function setXml(bool $xml = false)
    {
        $this->xml = $xml;
    }

    public function setType(string $type = '')
    {
        $this->type = $type;
    }

    protected function setRoutes()
    {
        global $routes;
        $this->routes = $routes;

        return $this;
    }

    protected function formatDataForXmlOutput($data)
    {
        switch ($this->type) {
            case 'breedOneDimensional': // /breeds/list/xml
                $data->breeds['breed'] = $data->message;
                unset($data->message);
                break;
            case 'breedTwoDimensional': // /breeds/list/all/xml
                $data->breeds['breed'] = array_keys($data->message);
                $subBreeds = array_filter(array_map('array_filter', $data->message));
                $data->{'breedcategories'} = $subBreeds;
                unset($data->message);
                break;
            case 'imageSingle': // /breeds/image/random/xml
                $data->images['image'] = [$data->message];
                unset($data->message);
                break;
            case 'imageMulti': // /breed/bulldog/french/images/xml
                $data->images['image'] = $data->message;
                unset($data->message);
                break;
            case 'breedInfo': // /breed/spaniel/cocker/xml
                $data->breed = $data->message;
                unset($data->message);
                break;
        }

        return $data;
    }

    protected function response($data, $status = 200)
    {
        if (!$this->xml) {
            $response = new JsonResponse($data, $status);
        }
        if ($this->xml) {
            $response = new Response(ArrayToXml::convert((array) $this->formatDataForXmlOutput($data)), $status);
            $response->headers->set('Content-Type', 'xml');
        }

        $response->headers->set('Access-Control-Allow-Origin', '*');
        return $response;
    }

    // the domain and port and protocol
    private function baseUrl()
    {
        $actual_link = (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'];

        if (php_sapi_name() == 'cli' && $_SERVER['SERVER_PORT'] !== 80 && $_SERVER['SERVER_PORT'] !== 443) {
            $actual_link .= ':'.$_SERVER['SERVER_PORT'];
        }

        return $actual_link;
    }

    // set the url to the images
    private function setimageUrl()
    {
        //$this->imageUrl = $this->baseUrl().'/api/img/'; // must have trailing slash for now
        $this->imageUrl = 'https://images.dog.ceo/breeds/';
    }

    private function imagePath($test = false)
    {
        $path = realpath(__DIR__.'/../img');

        if (!$path) {
            return false;
        }

        return $path;
    }

    // return the path to the images
    protected function getBreedsDirectory()
    {
        return $this->imagePath;
    }

    // get an aray of all the breed directories, set the var
    // should only be called from construct, cached
    protected function returnBreedDirs()
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

        return false;
    }

    private function niceBreedNameFromFolder($folder = false)
    {
        $strings = explode('-', $folder);
        $strings = array_reverse($strings);
        $strings = implode(' ', $strings);
        return ucfirst($strings);
    }

    protected function niceBreedAltFromFolder($folder = false)
    {
        $alt = $this->niceBreedNameFromFolder($folder) . ' dog';
        return $alt;
    }

    protected function breedFolderFromUrl($url)
    {
        $explodedPath = explode('/', $url);
        return $explodedPath[count($explodedPath) - 2];
    }

    // json response of 2d breeds array
    public function breedListAll()
    {
        $responseArray = (object) ['status' => 'error', 'code' => '404', 'message' => 'No breeds found'];

        $allBreeds = $this->getAllBreeds();

        if (count($allBreeds)) {
            $responseArray = (object) ['status' => 'success', 'message' => $allBreeds];
        }

        return $this->response($responseArray);
    }

    // json response of master breeds
    public function breedList()
    {
        $responseArray = (object) ['status' => 'success', 'message' => $this->getMasterBreeds()];

        return $this->response($responseArray);
    }

    // json response of sub breeds
    public function breedListSub($breed = null)
    {
        $status = 404;
        $responseArray = (object) ['status' => 'error', 'code' => '404', 'message' => 'Breed not found'];

        $breedSubList = $this->getSubBreeds($breed);

        if (is_array($breedSubList)) {
            $status = 200;
            $responseArray = (object) ['status' => 'success', 'message' => $breedSubList];
        }

        return $this->response($responseArray, $status);
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
    protected function getAllImages($imagesDir)
    {
        $images = $this->cache->storeAndReturn('getAllImages.'.md5(serialize($imagesDir)), 60, function () use ($imagesDir) {
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
    private function getRandomImage($imagesDir, $amount = 0)
    {
        $images = $this->getAllImages($imagesDir);

        if ($amount > 0) {
            $total = count($images);

            if ($amount > $total) {
                $amount = $total;
            }

            // get the keys
            $randomKeys = array_rand($images, $amount);

            // lolphp, for some reason array_rand returns mixes types...
            if ($amount === 1) {
                $randomKeys = [$randomKeys];
            }

            // get the values
            return array_values(array_intersect_key($images, array_flip($randomKeys)));
        }

        return $images[array_rand($images)];
    }

    // return an image based on the $breed string passed
    public function breedImage($breed = null, $breed2 = null, bool $all = false, int $amount = 0)
    {
        // default response, 404
        $status = 404;
        $responseArray = (object) ['status' => 'error', 'code' => '404', 'message' => 'Breed not found'];

        $match = $this->matchBreedString($breed, $breed2);
        if ($match) {
            // return all images?
            if ($all) {
                $images = $this->getAllImages($match);
                foreach ($images as $key => $image) {
                    $directory = $this->breedFolderFromUrl($image);

                    if (!$this->alt) {
                        $images[$key] = $this->imageUrl.$directory.'/'.basename($image);
                    } else {
                        $images[$key] = [
                            'url' => $this->imageUrl.$directory.'/'.basename($image),
                            'altText'   => $this->niceBreedAltFromFolder($directory),
                        ];
                    }
                }
                $status = 200;
                $responseArray = (object) ['status' => 'success', 'message' => $images];
            } else {
                if ($amount > 0) {
                    $images = $this->getRandomImage($match, $amount);

                    foreach ($images as $key => $image) {
                        $directory = $this->breedFolderFromUrl($image);
                        if (!$this->alt) {
                            $images[$key] = $this->imageUrl.$directory.'/'.basename($image);
                        } else {
                            $images[$key] = [
                                'url' => $this->imageUrl.$directory.'/'.basename($image),
                                'altText'   => $this->niceBreedAltFromFolder($directory),
                            ];
                        }
                    }

                    $status = 200;
                    $responseArray = (object) ['status' => 'success', 'message' => $images];
                } else {
                    // otherwise, we just want one image
                    $image = $this->getRandomImage($match, $amount);
                    $directory = $this->breedFolderFromUrl($image);
                    // json response with url to image
                    if ($image !== false) {
                        $status = 200;
                        $responseArray = (object) ['status' => 'success', 'message' => $this->imageUrl.$directory.'/'.basename($image)];
                        if (!$this->alt) {
                            $responseArray = (object) ['status' => 'success', 'message' => $this->imageUrl.$directory.'/'.basename($image)];
                        } else {
                            $responseArray = (object) [
                                'status' => 'success',
                                'message' => [
                                    'url' => $this->imageUrl.$directory.'/'.basename($image),
                                    'altText' => $this->niceBreedAltFromFolder($directory)
                                ]
                            ];
                        }
                    }
                }
            }
        }

        return $this->response($responseArray, $status);
    }

    // get multiple random images of any breed
    public function breedAllRandomImages($amount = 0)
    {
        // convert to int
        $amount = (int) $amount;

        //exit early if count was not supplied
        if ($amount == 0) {
            return $this->breedAllRandomImage();
        }
        $breedDirectories = $this->getBreedDirs();
        $images = [];

        //ensure amount never excedes directory count
        $amount = $amount > count($breedDirectories) ? count($breedDirectories) : $amount;

        for ($i = 0; $i < $amount; $i++) {
            $image = $this->getRandomImage($breedDirectories[mt_rand(0, count($breedDirectories) - 1)]);
            $directory = $this->breedFolderFromUrl($image);
            if (!$this->alt) {
                $images[] = $this->imageUrl.$directory.'/'.basename($image);
            } else {
                $images[] = [
                    'url' => $this->imageUrl.$directory.'/'.basename($image),
                    'altText'   => $this->niceBreedAltFromFolder($directory),
                ];
            }
        }

        $responseArray = (object) ['status' => 'success', 'message' => $images];

        return $this->response($responseArray);
    }

    // return a random image of any breed
    public function breedAllRandomImage()
    {
        // pick a random dir
        $randomBreedDir = $this->getBreedDirs()[array_rand($this->getBreedDirs())];

        // pick a random image from that dir
        $image = $this->getRandomImage($randomBreedDir);

        $directory = $this->breedFolderFromUrl($image);

        if (!$this->alt) {
            $responseArray = (object) ['status' => 'success', 'message' => $this->imageUrl.$directory.'/'.basename($image)];
        } else {
            $responseArray = (object) [
                'status' => 'success',
                'message' => [
                    'url' => $this->imageUrl.$directory.'/'.basename($image),
                    'altText' => $this->niceBreedAltFromFolder($directory)
                ]
            ];
        }

        return $this->response($responseArray);
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
    private function arrayWhitelist(array $array, $whitelist)
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
            try {
                $array = Yaml::parse(file_get_contents($path));
            } catch (ParseException $exception) {
                die('Unable to parse the YAML string: '. $exception->getMessage());
            }

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
        $status = 404;
        $responseArray = (object) ['status' => 'error', 'code' => '404', 'message' => 'Breed not found'];

        $content = $this->getBreedText($breed, $breed2);

        if ($content !== false) {
            $status = 200;
            $responseArray = (object) ['status' => 'success', 'message' => $content];
        }

        return $this->response($responseArray, $status);
    }
}
