<?php
// src/Util/BreedUtil.php
namespace App\Util;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class BreedUtil
{
    protected $endpointUrl;

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

    public function getAllBreeds()
    {
        $suffix = 'breeds/list/all';

        $url = $this->endpointUrl . $suffix;

        return $this->cacheAndReturn($url, 3600);
    }

    public function getTopLevelBreeds()
    {
        $suffix = 'breeds/list';

        $url = $this->endpointUrl . $suffix;

        return $this->cacheAndReturn($url, 3600);
    }
}
