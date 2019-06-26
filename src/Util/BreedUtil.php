<?php

// src/Util/BreedUtil.php

namespace App\Util;

use Spatie\ArrayToXml\ArrayToXml;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\ItemInterface;

class BreedUtil
{
    // empty vars
    protected $cache;
    protected $client;
    protected $response;
    protected $responseCode;

    // default vars
    protected $xmlEnable = false;
    protected $endpointUrl = '';
    protected $cacheSeconds = 2 * 24 * 60 * 60; // 2 weeks in seconds
    protected $breedDelimiter = '-';

    // error messages
    protected $masterBreedNotFoundMessage = 'Breed not found (master breed does not exist)';
    protected $subBreedNotFoundMessage = 'Breed not found (sub breed does not exist)';
    protected $breedFileNotFound = 'Breed not found (No info file for this breed exists)';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setClient(new \GuzzleHttp\Client());
        $this->cache = new FilesystemAdapter();

        // uncomment to emulate unit test behaviour:
        //$this->disableCache();
        //$this->setClient(new \App\Util\MockApi());
    }

    /**
     * Enable xml output by switching the class var to true.
     *
     * @return BreedUtil $this
     */
    public function xmlOutputEnable(): ?self
    {
        $this->xmlEnable = true;

        return $this;
    }

    /**
     * Set the url to the lambda endpoint
     * e.g http://dog-api.lambda.aws.com/dev/.
     *
     * @param string $url
     *
     * @return BreedUtil $this
     */
    public function setEndpointUrl(string $url): ?self
    {
        $this->endpointUrl = $url;

        return $this;
    }

    /**
     * Set the guzzle client used to run gets on the endpoints.
     *
     * @param \GuzzleHttp\Client $client
     *
     * @return BreedUtil $this
     */
    public function setClient($client): ?self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Clear the entire cache, used in unit tests.
     *
     * @return BreedUtil $this
     */
    public function clearCache()
    {
        $this->cache->clear();

        return $this;
    }

    /**
     * Get a url, query the cache first, run guzzle if no hit.
     *
     * @param string  $url     Full url to be got with guzzle
     * @param int|int $seconds How many seconds to cache the response for
     *
     * @return object Either the json_decoded response or an object containing the error message
     */
    private function cacheAndReturn(string $url = '', int $seconds = 3600): ?object
    {
        $self = $this;

        // The callable will only be executed on a cache miss.
        $value = $this->cache->get(md5($url), function (ItemInterface $item) use ($self, $url, $seconds) {
            $item->expiresAfter($seconds);

            return $self->getWithGuzzle($url);
        });

        // set 200 here, request was successful
        $this->responseCode = Response::HTTP_OK;

        return $value;
    }

    /**
     * Get a url using guzzle.
     *
     * @param string $url
     *
     * @return object Either the json_decoded response or an object containing the error message
     */
    private function getWithGuzzle(string $url): ?object
    {
        try {
            $res = $this->client->request('GET', $url);

            return json_decode($res->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return (object) [
                'status'  => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * List all breed names including sub breeds.
     *
     * @return BreedUtil $this
     */
    public function getAllBreeds(): ?self
    {
        $suffix = 'breeds/list/all';

        $url = $this->endpointUrl.$suffix;

        $this->response = $this->cacheAndReturn($url, $this->cacheSeconds);

        return $this;
    }

    /**
     * Get random breed including any sub breeds.
     *
     * @return BreedUtil $this
     */
    public function getAllBreedsRandomSingle(): ?self
    {
        $this->response->message = $this->randomItemFromAssociativeArray((array) $this->getAllBreeds()->arrayResponse()->message);

        return $this;
    }

    /**
     * Get multiple random breeds including any sub breeds.
     *
     * @param int|int $amount How many breeds to return
     *
     * @return BreedUtil $this
     */
    public function getAllBreedsRandomMultiple(int $amount): ?self
    {
        $this->response->message = $this->randomItemsFromArray((array) $this->getAllBreeds()->arrayResponse()->message, $amount, true);

        return $this;
    }

    /**
     * List all master breed names.
     *
     * @return BreedUtil $this
     */
    public function getAllTopLevelBreeds(): ?self
    {
        $suffix = 'breeds/list';

        $url = $this->endpointUrl.$suffix;

        $this->response = $this->cacheAndReturn($url, $this->cacheSeconds);

        return $this;
    }

    /**
     * Get single random master breed.
     *
     * @return BreedUtil $this
     */
    public function getAllTopLevelBreedsRandomSingle(): ?self
    {
        $this->response->message = $this->randomItemFromArray((array) $this->getAllTopLevelBreeds()->arrayResponse()->message);

        return $this;
    }

    /**
     * Get multiple random master breeds.
     *
     * @param int|int $amount How many breeds to return
     *
     * @return BreedUtil $this
     */
    public function getAllTopLevelBreedsRandomMultiple(int $amount): ?self
    {
        $this->response->message = $this->randomItemsFromArray((array) $this->getAllTopLevelBreeds()->arrayResponse()->message, $amount);

        return $this;
    }

    /**
     * List sub breeds of a master breed.
     *
     * @param string $breed The master breed
     *
     * @return BreedUtil $this
     */
    public function getAllSubBreeds(string $breed): ?self
    {
        if ($this->masterBreedExists($breed)) {
            $suffix = "breed/$breed/list";

            $url = $this->endpointUrl.$suffix;

            $this->response = $this->cacheAndReturn($url, $this->cacheSeconds);
        } else {
            $this->setNotFoundResponse($this->masterBreedNotFoundMessage);
        }

        return $this;
    }

    public function getAllSubBreedsRandomSingle(string $breed): ?object
    {
        $this->response->message = $this->randomItemFromArray((array) $this->getAllSubBreeds($breed)->arrayResponse()->message);

        return $this;
    }

    public function getAllSubBreedsRandomMulti(string $breed, $amount): ?object
    {
        $this->response->message = $this->randomItemsFromArray((array) $this->getAllSubBreeds($breed)->arrayResponse()->message, $amount);

        return $this;
    }

    /**
     * Get all master breed images.
     *
     * @param string $breed The master breed
     *
     * @return BreedUtil $this
     */
    public function getTopLevelImages(string $breed): ?self
    {
        if ($this->masterBreedExists($breed)) {
            $suffix = "breed/$breed/images";

            $url = $this->endpointUrl.$suffix;

            $this->response = $this->cacheAndReturn($url, $this->cacheSeconds);
        } else {
            $this->setNotFoundResponse($this->masterBreedNotFoundMessage);
        }

        return $this;
    }

    /**
     * Get random image from a breed (and all its sub-breeds).
     *
     * @param string $breed The master breed
     *
     * @return BreedUtil $this
     */
    public function getRandomTopLevelImage(string $breed): ?self
    {
        $images = $this->getTopLevelImages($breed)->arrayResponse()->message;

        if ($this->response->status === 'success') {
            $this->response->message = $this->randomItemFromArray($images);
        }

        return $this;
    }

    /**
     * Get multiple random images from a breed (and all its sub-breeds).
     *
     * @param string  $breed  The master breed
     * @param int|int $amount How many images to return
     *
     * @return BreedUtil $this
     */
    public function getRandomTopLevelImages(string $breed, int $amount): ?self
    {
        $images = $this->getTopLevelImages($breed)->arrayResponse()->message;

        if ($this->response->status === 'success') {
            $this->response->message = $this->randomItemsFromArray($images, $amount);
        }

        return $this;
    }

    /**
     * Get all images from a sub breed.
     *
     * @param string $breed1 The master breed
     * @param string $breed2 The sub breed
     *
     * @return BreedUtil $this
     */
    public function getSubLevelImages(string $breed1, string $breed2): ?self
    {
        if ($this->masterBreedExists($breed1)) {
            if ($this->subBreedExists($breed1, $breed2)) {
                $suffix = "breed/$breed1/$breed2/images";

                $url = $this->endpointUrl.$suffix;

                $this->response = $this->cacheAndReturn($url, $this->cacheSeconds);
            } else {
                $this->setNotFoundResponse($this->subBreedNotFoundMessage);
            }
        } else {
            $this->setNotFoundResponse($this->masterBreedNotFoundMessage);
        }

        return $this;
    }

    /**
     * Get random image from a sub breed.
     *
     * @param string $breed1 The master breed
     * @param string $breed2 The sub breed
     *
     * @return BreedUtil $this
     */
    public function getRandomSubLevelImage(string $breed1, string $breed2): ?self
    {
        $images = $this->getSubLevelImages($breed1, $breed2)->arrayResponse()->message;

        if ($this->response->status === 'success') {
            $this->response->message = $this->randomItemFromArray($images);
        }

        return $this;
    }

    /**
     * Get multiple random images from a sub breed.
     *
     * @param string  $breed1 The master breed
     * @param string  $breed2 The sub breed
     * @param int|int $amount How many images to return
     *
     * @return BreedUtil $this
     */
    public function getRandomSubLevelImages(string $breed1, string $breed2, int $amount): ?self
    {
        $images = $this->getSubLevelImages($breed1, $breed2, $amount)->arrayResponse()->message;

        if ($this->response->status === 'success') {
            $this->response->message = $this->randomItemsFromArray($images, $amount);
        }

        return $this;
    }

    /**
     * Random image from any breed.
     *
     * @return BreedUtil $this
     */
    public function getRandomImage(): ?self
    {
        $breeds = $this->collapseArrayWithString($this->getAllBreeds()->arrayResponse()->message, $this->breedDelimiter);

        $randomBreed = $this->randomItemFromArray($breeds);

        $this->response->message = $this->getRandomImageWithCollapsedBreed($randomBreed);

        return $this;
    }

    /**
     * Get multiple random images from any breed (max. 50).
     *
     * @param int|int $amount How many images to return
     *
     * @return BreedUtil $this
     */
    public function getRandomImages(int $amount): ?self
    {
        // maximum of 50 random images
        if ($amount > 50) {
            $amount = 50;
        }

        $breeds = $this->collapseArrayWithString($this->getAllBreeds()->arrayResponse()->message, $this->breedDelimiter);

        $randomImages = [];

        for ($i = 0; $i < $amount; $i++) {
            $randomBreed = $this->randomItemFromArray($breeds);
            $randomImages[] = $this->getRandomImageWithCollapsedBreed($randomBreed);
        }

        $this->response->message = $randomImages;

        return $this;
    }

    /**
     * Get a random image from either a master or master/sub based on a string.
     *
     * @param  string Collapsed breed e.g affenpischer or collie-border
     *
     * @return string The image
     */
    private function getRandomImageWithCollapsedBreed(string $collapsedBreed): ?string
    {
        $exploded = explode($this->breedDelimiter, $collapsedBreed);
        if (!isset($exploded[1])) {
            return $this->getRandomTopLevelImage($exploded[0])->arrayResponse()->message;
        } else {
            return $this->getRandomSubLevelImage($exploded[0], $exploded[1])->arrayResponse()->message;
        }
    }

    /**
     * Collapse a 2d array or object of strings into a 1d array with a string delimeter.
     *
     * @param object $object    2d array or object
     * @param string $delimiter What string to use when joining the strings
     *
     * @return array $result    The 1d array
     */
    private function collapseArrayWithString(object $object, string $delimiter): ?array
    {
        $result = [];

        foreach ($object as $master => $subs) {
            if (!count($subs)) {
                $result[] = $master;
            } else {
                foreach ($subs as $sub) {
                    $result[] = $master.$delimiter.$sub;
                }
            }
        }

        return $result;
    }

    /**
     * Get a single random item from a non associative array.
     *
     * @param array $array
     *
     * @return string Whatever item gets picked
     */
    private function randomItemFromArray(array $array): ?string
    {
        return $array[array_rand($array)];
    }

    /**
     * Get a single random item from an associative array.
     *
     * @param  array The array to select the item from
     *
     * @return array Key/value
     */
    private function randomItemFromAssociativeArray(array $array): ?array
    {
        $key = array_rand($array);
        $value = $array[$key];

        return [$key => $value];
    }

    /**
     * Get multiple random items from the array.
     *
     * @param array     $array      The array to select the items from
     * @param int       $amount     The amount of items to return
     * @param bool|bool $retainKeys If set to true keys will be returned as well
     *
     * @return array
     */
    private function randomItemsFromArray(array $array, int $amount, bool $retainKeys = false): ?array
    {
        // array_rand arg2 has to be larger than 1
        if ($amount < 1) {
            $amount = 10;
        }

        // count total items in array
        $total = count($array);

        // reset $amount if its higher than the total
        if ($amount > $total) {
            $amount = $total;
        }

        // get the keys
        $randomKeys = array_rand($array, $amount);

        // lolphp, array_rand returns mixed types...
        if ($amount === 1) {
            $randomKeys = [$randomKeys];
        }

        // get the values, keep the keys intact
        if ($retainKeys === true) {
            $values = [];
            foreach ($randomKeys as $key) {
                $values[$key] = $array[$key];
            }

            return $values;
        }

        // get the values
        return array_values(array_intersect_key($array, array_flip($randomKeys)));
    }

    /**
     * Check if the master breed exists.
     *
     * @param string $breed The master breed
     *
     * @return bool
     */
    private function masterBreedExists(string $breed): ?bool
    {
        return in_array($breed, $this->getAllTopLevelBreeds()->arrayResponse()->message);
    }

    /**
     * Check if the sub breed exists.
     *
     * @param string $breed1 The master breed
     * @param string $breed2 The sub breed
     *
     * @return bool
     */
    private function subBreedExists(string $breed1, string $breed2): ?bool
    {
        return in_array($breed2, $this->getAllSubBreeds($breed1)->arrayResponse()->message);
    }

    /**
     * Sets a basic not found response and message.
     *
     * @param string $message The error message
     *
     * @return BreedUtil $this
     */
    private function setNotFoundResponse(string $message): ?self
    {
        $this->responseCode = Response::HTTP_NOT_FOUND;

        $this->response = (object) [
            'status'    => 'error',
            'message'   => $message,
            'code'      => $this->responseCode,
        ];

        return $this;
    }

    /**
     * Gets a response object.
     *
     * @return object
     */
    public function getResponse(): ?object
    {
        if ($this->xmlEnable) {
            return $this->xmlResponse();
        }

        return $this->jsonResponse();
    }

    /**
     * Gets a response object and adds some cloudflare cache headers.
     *
     * @return object
     */
    public function getResponseWithCacheHeaders(): ?object
    {
        $response = $this->getResponse();
        // cache on cloudflare for 6 hours, cache in browser for 30 minutes
        $response->headers->set('Cache-Control', 's-maxage=21600, max-age=1800');

        return $response;
    }

    /**
     * Gets a response object (JSON).
     *
     * @return JsonResponse
     */
    private function jsonResponse(): ?JsonResponse
    {
        $response = new JsonResponse($this->response);
        $response->setStatusCode($this->responseCode);
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }

    /**
     * Gets a response object (XML).
     *
     * @return Response
     */
    private function xmlResponse(): ?Response
    {
        $response = new Response(ArrayToXml::convert($this->formatDataForXmlOutput()), $this->responseCode);
        $response->headers->set('Content-Type', 'application/xml');

        return $response;
    }

    /**
     * Gets a response object (raw object from guzzle json_decode).
     *
     * @return object
     */
    private function arrayResponse(): ?object
    {
        return $this->response;
    }

    /**
     * Formats the response and returns it ready for XML output.
     *
     * @todo It is probably possible to make this less dirty
     *
     * @return object Formatted response
     */
    private function formatDataForXmlOutput(): ?array
    {
        $responseType = $this->detectResponseType();
        $data = $this->response;
        $data->message = (array) $data->message;

        // restructure data a bit so that xml outputs correctly
        switch ($responseType) {
            case 'breedOneDimensional': // /breeds/list/xml
                $data->breeds['breed'] = $data->message;
                unset($data->message);
                break;
            case 'breedTwoDimensional': // /breeds/list/all/xml
                $data->breeds['breed'] = array_keys($data->message);
                $subBreeds = array_filter(array_map('array_filter', $data->message));
                $data->subbreeds = $subBreeds;
                $data->allbreeds = $data->message;
                unset($data->message);
                break;
            case 'imageSingle': // /breeds/image/random/xml
                // deal with alts
                if (isset($data->message['alt'])) {
                    $data->message['alt'] = $data->message['altText'];
                    unset($data->message['altText']);
                }
                $data->images['image'] = [$data->message];
                unset($data->message);
                break;
            case 'imageMulti': // /breed/bulldog/french/images/xml
                // deal with alts
                foreach ($data->message as $key => $value) {
                    $data->message[$key]['alt'] = $data->message[$key]['altText'];
                    unset($data->message[$key]['altText']);
                }
                $data->images['image'] = $data->message;
                unset($data->message);
                break;
            case 'breedInfo': // /breed/spaniel/cocker/xml
                $data->breed = $data->message;
                unset($data->message);
                break;
        }

        return (array) $data;
    }

    /**
     * Detects what typs of response we have based on its contents.
     *
     * @todo This is maybe not the best solution
     *
     * @return string Textual representation of the response type
     */
    private function detectResponseType()
    {
        if (isset($this->response->message->info) && isset($this->response->message->name)) {
            return 'breedInfo';
        }

        // if there's an alt tag in an array
        if (is_array($this->response->message) && isset($this->response->message[0]['altText'])) {
            return 'imageMulti';
        }

        // if there's an alt tag on a single item
        if (is_array($this->response->message) && isset($this->response->message['altText'])) {
            return 'imageSingle';
        }

        // first item of array starts with 'http' and is a string
        if (is_array($this->response->message) && isset($this->response->message[0]) && is_string($this->response->message[0]) && substr($this->response->message[0], 0, 4) === 'http') {
            return 'imageMulti';
        }

        // response starts with 'http' and is a string
        if (is_string($this->response->message) && substr($this->response->message, 0, 4) === 'http') {
            return 'imageSingle';
        }

        if ($this->arrayIsMultiDimensional($this->response->message)) {
            return 'breedTwoDimensional';
        }

        return 'breedOneDimensional';
    }

    /**
     * Checks if an array is multi dimensional.
     *
     * @param array $array
     *
     * @return bool
     */
    private function arrayIsMultiDimensional($array): ?bool
    {
        // this line doesnt work for $array = ['item' => []]
        //return count($array) == count($array, COUNT_RECURSIVE);
        foreach ($array as $v) {
            if (is_array($v)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the info text for a master breed.
     *
     * @param string $breed The master breed
     *
     * @return BreedUtil $this
     */
    public function masterText(string $breed): ?self
    {
        if ($this->masterBreedExists($breed)) {
            $suffix = "breed/$breed";

            $url = $this->endpointUrl.$suffix;

            $this->response = $this->cacheAndReturn($url, $this->cacheSeconds);

            if ($this->response->status === 'error') {
                $this->setNotFoundResponse($this->breedFileNotFound);
            }
        } else {
            $this->setNotFoundResponse($this->masterBreedNotFoundMessage);
        }

        return $this;
    }

    /**
     * Get the info text for a sub breed.
     *
     * @param string $breed1 The master breed
     * @param string $breed2 The sub breed
     *
     * @return BreedUtil $this
     */
    public function subText(string $breed1, string $breed2): ?self
    {
        if ($this->masterBreedExists($breed1)) {
            if ($this->subBreedExists($breed1, $breed2)) {
                $suffix = "breed/$breed1/$breed2";

                $url = $this->endpointUrl.$suffix;

                $this->response = $this->cacheAndReturn($url, $this->cacheSeconds);

                if ($this->response->status === 'error') {
                    $this->setNotFoundResponse($this->breedFileNotFound);
                }
            } else {
                $this->setNotFoundResponse($this->subBreedNotFoundMessage);
            }
        } else {
            $this->setNotFoundResponse($this->masterBreedNotFoundMessage);
        }

        return $this;
    }

    /**
     * Adds the alt tags to a response containing images.
     *
     * @return BreedUtil $this
     */
    private function addAltTags(): ?self
    {
        if (!is_array($this->response->message) && is_string($this->response->message)) {
            // single image response - this is never called at the moment
            $this->response->message = [
                'url'       => $this->response->message,
                'altText'   => $this->niceBreedAltFromFolder($this->breedFolderFromUrl($this->response->message)),
            ];
        } else {
            // multi image response
            foreach ($this->response->message as $key => $image) {
                $this->response->message[$key] = [
                    'url'       => $image,
                    'altText'   => $this->niceBreedAltFromFolder($this->breedFolderFromUrl($image)),
                ];
            }
        }

        return $this;
    }

    /**
     * Makes a nice looking breed name from a folder name
     * e.g 'border-collie' becomes 'border collie'.
     *
     * @param string $folder Breed name
     *
     * @return string
     */
    private function niceBreedNameFromFolder($folder = 'unknown-breed'): ?string
    {
        $strings = explode('-', $folder);
        $strings = array_reverse($strings);
        $strings = implode(' ', $strings);

        return ucfirst($strings);
    }

    /**
     * Makes a nice looking breed text for an alt tag
     * e.g 'border collie' becomes 'border collie dog'.
     *
     * @param string $folder Breed name
     *
     * @return string
     */
    private function niceBreedAltFromFolder(string $folder = 'unknown-breed'): ?string
    {
        $alt = $this->niceBreedNameFromFolder($folder).' dog';

        return $alt;
    }

    /**
     * Gets the breed name from the url
     * e.g '/api/border-collie/dog.jpg' becomes 'border-collie'.
     *
     * @param string $url The url
     *
     * @return string
     */
    private function breedFolderFromUrl($url): ?string
    {
        $explodedPath = explode('/', $url);

        return $explodedPath[count($explodedPath) - 2];
    }

    /**
     * Get all master breed images (with alt tags).
     *
     * @param string $breed The master breed
     *
     * @return BreedUtil $this
     */
    public function getTopLevelImagesWithAltTags(string $breed): ?self
    {
        $this->getTopLevelImages($breed)->addAltTags();

        return $this;
    }

    /**
     * Get multiple random images from a breed (and all its sub-breeds) with alt tags.
     *
     * @param string  $breed  The master breed
     * @param int|int $amount How many images to return
     *
     * @return BreedUtil $this
     */
    public function getRandomTopLevelImagesWithAltTags(string $breed, int $amount): ?self
    {
        $this->getRandomTopLevelImages($breed, $amount)->addAltTags();

        return $this;
    }

    /**
     * Get all images from a sub breed (with alt tags).
     *
     * @param string $breed1 The master breed
     * @param string $breed2 The sub breed
     *
     * @return BreedUtil $this
     */
    public function getSubLevelImagesWithAltTags(string $breed1, string $breed2): ?self
    {
        $this->getSubLevelImages($breed1, $breed2)->addAltTags();

        return $this;
    }

    /**
     * Get multiple random images from a sub breed (with alt tags).
     *
     * @param string  $breed1 The master breed
     * @param string  $breed2 The sub breed
     * @param int|int $amount How many images to return
     *
     * @return BreedUtil $this
     */
    public function getRandomSubLevelImagesWithAltTags(string $breed1, string $breed2, int $amount): ?self
    {
        $this->getRandomSubLevelImages($breed1, $breed2, $amount)->addAltTags();

        return $this;
    }

    /**
     * Get multiple random images from any breed (max. 50) with alt tags.
     *
     * @param int|int $amount How many images to return
     *
     * @return BreedUtil $this
     */
    public function getRandomImagesWithAltTags(int $amount): ?self
    {
        $this->getRandomImages($amount)->addAltTags();

        return $this;
    }
}
