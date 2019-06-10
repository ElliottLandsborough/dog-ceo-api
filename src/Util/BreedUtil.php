<?php
// src/Util/BreedUtil.php
namespace App\Util;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Spatie\ArrayToXml\ArrayToXml;

class BreedUtil
{
    protected $endpointUrl;
    protected $response;
    protected $responseCode;
    protected $breedDelimiter = '-';
    protected $cacheSeconds = 2 * 24 * 60 * 60; // 2 weeks in seconds
    protected $xmlEnable = false;

    // error messages
    protected $masterBreedNotFoundMessage = 'Breed not found (master breed does not exist)';
    protected $subBreedNotFoundMessage = 'Breed not found (sub breed does not exist)';
    protected $breedFileNotFound = 'Breed not found (No info file for this breed exists)';

    public function __construct()
    {
        $this->endpointUrl = $_ENV['DOG_CEO_LAMBDA_URI'];
        $this->xmlEnable = (Request::createFromGlobals()->headers->get('content-type') === 'application/xml');
    }

    protected function cacheAndReturn($url, $seconds): ?object
    {
        $self = $this;

        $cache = new FilesystemAdapter();

        // The callable will only be executed on a cache miss.
        $value = $cache->get(md5($url), function (ItemInterface $item) use ($self, $url, $seconds) {
            $item->expiresAfter($seconds);

            return $self->getWithGuzzle($url);
        });

        // set 200 here, request was successful
        $this->responseCode = Response::HTTP_OK;

        return $value;
    }

    protected function getWithGuzzle(string $url): ?Object
    {
        $client = new \GuzzleHttp\Client();

        try {
            $res = $client->request('GET', $url);

            return json_decode($res->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return (object) [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getAllBreeds(): ?self
    {
        $suffix = 'breeds/list/all';

        $url = $this->endpointUrl . $suffix;

        $this->response = $this->cacheAndReturn($url, $this->cacheSeconds);

        return $this;
    }

    public function getAllTopLevelBreeds(): ?self
    {
        $suffix = 'breeds/list';

        $url = $this->endpointUrl . $suffix;

        $this->response = $this->cacheAndReturn($url, $this->cacheSeconds);

        return $this;
    }

    public function getAllSubBreeds(string $breed): ?self
    {
        if ($this->masterBreedExists($breed)) {
            $suffix = "breed/$breed/list";

            $url = $this->endpointUrl . $suffix;

            $this->response = $this->cacheAndReturn($url, $this->cacheSeconds);
        } else {
            $this->setNotFoundResponse($this->masterBreedNotFoundMessage);
        }

        return $this;
    }

    public function getTopLevelImages(string $breed): ?self
    {
        if ($this->masterBreedExists($breed)) {
            $suffix = "breed/$breed/images";

            $url = $this->endpointUrl . $suffix;

            $this->response = $this->cacheAndReturn($url, $this->cacheSeconds);
        } else {
            $this->setNotFoundResponse($this->masterBreedNotFoundMessage);
        }

        return $this;
    }

    public function getRandomTopLevelImage(string $breed): ?self
    {
        $images = $this->getTopLevelImages($breed)->arrayResponse()->message;

        $this->response->message = $this->randomItemFromArray($images);

        return $this;
    }

    public function getRandomTopLevelImages(string $breed, int $amount): ?self
    {
        $images = $this->getTopLevelImages($breed)->arrayResponse()->message;

        $this->response->message = $this->randomItemsFromArray($images, $amount);

        return $this;
    }

    public function getSubLevelImages(string $breed1, string $breed2): ?self
    {
        if ($this->masterBreedExists($breed1)) {
            if ($this->subBreedExists($breed1, $breed2)) {
                $suffix = "breed/$breed1/$breed2/images";

                $url = $this->endpointUrl . $suffix;

                $this->response = $this->cacheAndReturn($url, $this->cacheSeconds);
            } else {
                $this->setNotFoundResponse($this->subBreedNotFoundMessage);
            }
        } else {
            $this->setNotFoundResponse($this->masterBreedNotFoundMessage);
        }

        return $this;
    }

    public function getRandomSubLevelImage(string $breed1, string $breed2): ?self
    {
        $images = $this->getSubLevelImages($breed1, $breed2)->arrayResponse()->message;

        $this->response->message = $this->randomItemFromArray($images);

        return $this;
    }

    public function getRandomSubLevelImages(string $breed1, string $breed2, int $amount): ?self
    {
        $images = $this->getSubLevelImages($breed1, $breed2, $amount)->arrayResponse()->message;

        $this->response->message = $this->randomItemsFromArray($images, $amount);

        return $this;
    }

    public function getRandomImage(): ?self
    {
        $breeds = $this->collapseArrayWithString($this->getAllBreeds()->arrayResponse()->message, $this->breedDelimiter);

        $randomBreed = $this->randomItemFromArray($breeds);

        $this->response->message = $this->getRandomImageWithCollapsedBreed($randomBreed);

        return $this;
    }
    
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

    private function getRandomImageWithCollapsedBreed(string $collapsedBreed)
    {
        $exploded = explode($this->breedDelimiter, $collapsedBreed);
        if (!isset($exploded[1])) {
            return $this->getRandomTopLevelImage($exploded[0])->arrayResponse()->message;
        } else {
            return $this->getRandomSubLevelImage($exploded[0], $exploded[1])->arrayResponse()->message;
        }
    }

    private function collapseArrayWithString(object $object, string $delimiter): ?array
    {
        $result = [];

        foreach ($object as $master => $subs) {
            if (!count($subs)) {
                $result[] = $master;
            } else {
                foreach ($subs as $sub) {
                    $result[] = $master . $delimiter . $sub;
                }
            }
        }

        return $result;
    }

    private function randomItemFromArray(array $array): ?string
    {
        return $array[array_rand($array)];
    }

    private function randomItemsFromArray(array $array, int $amount): ?array
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

        // get the values
        return array_values(array_intersect_key($array, array_flip($randomKeys)));
    }

    private function masterBreedExists(string $breed): ?bool
    {
        return in_array($breed, $this->getAllTopLevelBreeds()->arrayResponse()->message);
    }

    private function subBreedExists(string $breed1, string $breed2): ?bool
    {
        return in_array($breed2, $this->getAllSubBreeds($breed1)->arrayResponse()->message);
    }

    private function setNotFoundResponse(string $message): ?self
    {
        $this->responseCode = Response::HTTP_NOT_FOUND;

        $this->response = [
            'status' => 'error',
            'body'   => $message,
        ];

        return $this;
    }

    public function getResponse(): ?object
    {
        if ($this->xmlEnable) {
            return $this->xmlResponse();
        }

        return $this->jsonResponse();
    }

    private function jsonResponse(): ?JsonResponse
    {
        $response = new JsonResponse($this->response);
        $response->setStatusCode($this->responseCode);
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }

    private function xmlResponse(): ?Response
    {
        $response = new Response(ArrayToXml::convert($this->formatDataForXmlOutput()), $this->responseCode);
        $response->headers->set('Content-Type', 'xml');

        return $response;
    }

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
     * @todo This is maybe not the best solution
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

    private function arrayIsMultiDimensional($array): ?bool
    {
        //return count($array) == count($array, COUNT_RECURSIVE);
        foreach ($array as $v) {
            if (is_array($v)) {
                return true;
            }
        }
        return false;
    }

    private function arrayResponse(): ?object
    {
        return $this->response;
    }

    /**
     *
     */
    public function masterText(string $breed): ?self
    {

        if ($this->masterBreedExists($breed)) {
            $suffix = "breed/$breed";

            $url = $this->endpointUrl . $suffix;

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
     *
     */
    public function subText(string $breed1, string $breed2): ?self
    {
        if ($this->masterBreedExists($breed1)) {
            if ($this->subBreedExists($breed1, $breed2)) {
                $suffix = "breed/$breed1/$breed2";

                $url = $this->endpointUrl . $suffix;

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

    private function niceBreedNameFromFolder($folder = false): ?string
    {
        $strings = explode('-', $folder);
        $strings = array_reverse($strings);
        $strings = implode(' ', $strings);
        return ucfirst($strings);
    }

    private function niceBreedAltFromFolder($folder = false): ?string
    {
        $alt = $this->niceBreedNameFromFolder($folder).' dog';
        return $alt;
    }

    private function breedFolderFromUrl($url): ?string
    {
        $explodedPath = explode('/', $url);
        return $explodedPath[count($explodedPath) - 2];
    }

    public function getTopLevelImagesWithAltTags(string $breed): ?self
    {
        $this->getTopLevelImages($breed)->addAltTags();

        return $this;
    }

    public function getRandomTopLevelImagesWithAltTags(string $breed, int $amount): ?self
    {
        $this->getRandomTopLevelImages($breed, $amount)->addAltTags();

        return $this;
    }

    public function getSubLevelImagesWithAltTags(string $breed1, string $breed2): ?self
    {
        $this->getSubLevelImages($breed1, $breed2)->addAltTags();

        return $this;
    }

    public function getRandomSubLevelImagesWithAltTags(string $breed1, string $breed2, int $amount): ?self
    {
        $this->getRandomSubLevelImages($breed1, $breed2, $amount)->addAltTags();

        return $this;
    }
    
    public function getRandomImagesWithAltTags(int $amount): ?self
    {
        $this->getRandomImages($amount)->addAltTags();

        return $this;
    }
}
