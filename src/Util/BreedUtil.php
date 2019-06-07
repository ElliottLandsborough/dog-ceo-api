<?php
// src/Util/BreedUtil.php
namespace App\Util;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class BreedUtil
{
    protected $endpointUrl;
    protected $response;
    protected $responseCode;
    protected $breedDelimiter = '-';
    protected $cacheSeconds = 2 * 24 * 60 * 60; // 2 weeks in seconds

    // error messages
    protected $masterBreedNotFoundMessage = 'Breed not found (master breed does not exist).';
    protected $subBreedNotFoundMessage = 'Breed not found (sub breed does not exist).';

    public function __construct()
    {
        $this->endpointUrl = $_ENV['DOG_CEO_LAMBDA_URI'];
    }

    protected function cacheAndReturn($url, $seconds)
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
            // was some sort of 404 or similar?
            echo $e->getMessage();
            die;
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

        // lolphp, for some reason array_rand returns mixed types...
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

    public function jsonResponse(): ?JsonResponse
    {
        $response = new JsonResponse($this->response);
        $response->setStatusCode($this->responseCode);
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }

    private function arrayResponse(): ?object
    {
        return $this->response;
    }

    public function masterText(string $breed): ?self
    {
        if ($this->masterBreedExists($breed)) {
            $suffix = "breed/$breed";

            $url = $this->endpointUrl . $suffix;

            $this->response = $this->cacheAndReturn($url, $this->cacheSeconds);
        } else {
            $this->setNotFoundResponse($this->masterBreedNotFoundMessage);
        }

        return $this;
    }

    /**
     * @route("/breed/{breed1}/{breed2}")
     */
    public function subText(string $breed1, string $breed2): ?self
    {
        if ($this->masterBreedExists($breed1)) {
            if ($this->subBreedExists($breed1, $breed2)) {
                $suffix = "breed/$breed1/$breed2";

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
}
