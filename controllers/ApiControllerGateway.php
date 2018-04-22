<?php

namespace controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use models\Cache;

class ApiControllerGateway extends ApiController
{
    private $cache;
    private $minutes = 600; // how many minutes to cache for on live

    public function __construct()
    {
        $this->cache = new Cache();

        if ($_SERVER['SERVER_NAME'] !== 'dog.ceo') {
            $this->minutes = 1;
        }
    }

    private function apiGet($endpoint)
    {
        $url = getenv('DOG_CEO_GATEWAY').$endpoint;

        $client = new \GuzzleHttp\Client();

        try {
            $res = $client->request('GET', $url);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $res = $e->getResponse();
        }

        return [
            'status' => $res->getStatusCode(),
            'body' => $res->getBody()->getContents(),
            'headers' => $res->getHeaders()
        ];
    }

    private function cacheEndPoint($endpoint)
    {
        return $this->cache->storeAndReturn(str_replace('/', '.', $endpoint), $this->minutes, function () use ($endpoint) {
            return $this->apiGet($endpoint);
        });
    }

    private function respond($endpointResponse)
    {
        $response = new JsonResponse();
        $response = $response->fromJsonString($endpointResponse['body'], $endpointResponse['status']);
        $response->headers->set('Access-Control-Allow-Origin', '*');
        if (isset($endpointResponse['headers']['cache-control'][0])) {
            $response->headers->set('Cache-Control', $endpointResponse['headers']['cache-control'][0]);
        }
        return $response;
    }

    public function selectRandomImageFromResponse($response)
    {
        if (isset($response['status']) && $response['status'] == 200 && isset($response['body'])) {
            $object = json_decode($response['body']);
            if (is_object($object)) {
                $object->message = $object->message[array_rand($object->message)];
                $response['body'] = json_encode($object);

                return $response;
            }
        }

        return false;
    }

    public function breedList()
    {
        return $this->respond($this->cacheEndPoint('breeds/list'));
    }

    public function breedListAll()
    {
        return $this->respond($this->cacheEndPoint('breeds/list/all'));
    }

    public function breedListSub($breed = 'hound')
    {
        return $this->respond($this->cacheEndPoint("breed/$breed/list"));
    }

    public function breedAllRandomImage()
    {
        return $this->respond($this->apiGet('breeds/image/random'));
    }

    public function breedAllRandomImages($count = 0)
    {
        return $this->respond($this->apiGet('breeds/image/random/$count'));
    }

    public function breedImage($breed = null, $breed2 = null, $all = false)
    {
        if (strlen($breed) && $breed2 === null) {
            $allImages = $this->cacheEndPoint("breed/$breed/images");

            // breed/{breed}/images
            if ($all === true) {
                return $this->respond($allImages);
            }

            // breed/{breed}/images/random
            if ($all === false) {
                $randomImageResponse = $this->selectRandomImageFromResponse($allImages);
                if ($randomImageResponse) {
                    return $this->respond($randomImageResponse);
                }

                // fallback
                return $this->respond($this->apiGet("breed/$breed/images/random"));
            }
        }

        if (strlen($breed) && strlen($breed2)) {
            $allImages = $this->cacheEndPoint("breed/$breed/$breed2/images");

            // breed/{breed}/{breed2}/images
            if ($all === true) {
                return $this->respond($allImages);
            }

            // breed/{breed}/{breed2}/images/random
            if ($all === false) {
                $randomImageResponse = $this->selectRandomImageFromResponse($allImages);
                if ($randomImageResponse) {
                    return $this->respond($randomImageResponse);
                }

                // fallback
                return $this->respond($this->apiGet("breed/$breed/$breed2/images/random"));
            }
        }
    }

    public function breedText($breed = null, $breed2 = null)
    {
        if ($breed2 === null) {
            // breed/{breed}
            return $this->respond($this->cacheEndPoint("breed/$breed"));
        } else {
            // breed/{breed}/{breed2}
            return $this->respond($this->cacheEndPoint("breed/$breed/$breed2"));
        }
    }
}
