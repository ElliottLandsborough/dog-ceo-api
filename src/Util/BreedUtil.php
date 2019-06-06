<?php
// src/Util/BreedUtil.php
namespace App\Util;

class BreedUtil
{
    protected $endpointUrl;

    public function __construct()
    {
        $this->endpointUrl = $_ENV['DOG_CEO_LAMBDA_URI'];
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

    public function getBreeds()
    {
        $suffix = 'breeds/list/all';

        $url = $this->endpointUrl . $suffix;

        return $this->getWithGuzzle($url);
    }
}