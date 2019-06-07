<?php
// src/Util/BreedUtil.php
namespace App\Util;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class BreedUtil
{
    protected $endpointUrl;
    protected $response;

    // error messages
    protected $masterBreedNotFoundMessage = 'Breed not found (master breed does not exist).';

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

        return $value;
    }

    protected function getWithGuzzle(string $url): ?Object
    {
        $client = new \GuzzleHttp\Client();

        try {
            $res = $client->request('GET', $url);
            /*
            return [
                'status'  => $res->getStatusCode(),
                'body'    => $res->getBody()->getContents(),
                'headers' => $res->getHeaders(),
            ];
            */
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

        $this->response = $this->cacheAndReturn($url, 3600);

        return $this;
    }

    public function getAllTopLevelBreeds(): ?self
    {
        $suffix = 'breeds/list';

        $url = $this->endpointUrl . $suffix;

        $this->response = $this->cacheAndReturn($url, 3600);

        return $this;
    }

    public function getAllSubBreeds(string $breed)
    {
        if ($this->masterBreedExists($breed)) {
            $suffix = "breed/$breed/list";

            $url = $this->endpointUrl . $suffix;

            $this->response = $this->cacheAndReturn($url, 3600);
        } else {
            $this->setNotFoundResponse($this->masterBreedNotFoundMessage);
        }

        return $this;
    }

    public function getTopLevelImages(string $breed)
    {
        if ($this->masterBreedExists($breed)) {
            $suffix = "breed/$breed/images";

            $url = $this->endpointUrl . $suffix;

            $this->response = $this->cacheAndReturn($url, 3600);
        } else {
            $this->setNotFoundResponse($this->masterBreedNotFoundMessage);
        }

        return $this;
    }

    public function masterBreedExists(string $breed): ?bool
    {
        return in_array($breed, $this->getAllTopLevelBreeds()->arrayResponse()->message);
    }

    public function subBreedExists(string $breed): ?bool
    {
        return in_array($breed, $this->getAllSubBreeds()->arrayResponse()->message);
    }

    public function setNotFoundResponse(string $message): ?self
    {
        $this->response = [
            'status' => 'error',
            'body'   => $message,
        ];

        return $this;
    }

    public function jsonResponse(): ?JsonResponse
    {
        $response = new JsonResponse($this->response);
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }

    public function arrayResponse(): ?object
    {
        return $this->response;
    }
}
