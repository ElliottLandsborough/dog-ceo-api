<?php
// src/Util/BreedUtil.php
namespace App\Util;

class MockApi extends \GuzzleHttp\Client
{
    protected $responses;

    public function __construct()
    {
        $responses = [
            'breeds/list/all' => '{"status":"success","message":{"affenpinscher":[],"bullterrier":["staffordshire"]}}',
            'breeds/list' => '{"status":"success","message":["affenpinscher","bullterrier"]}',
            'breed/affenpinscher/list' => '{"status":"success","message":[]}',
            'breed/bullterrier/list' => '{"status":"success","message":["staffordshire"]}',
            'breed/affenpinscher/images' => '{"status":"success","message":["https://images.dog.ceo/breeds/affenpinscher/image.jpg"]}',
            'breed/bullterrier/staffordshire/images' => '{"status":"success","message":["https://images.dog.ceo/breeds/bullterrier-staffordshire/image.jpg"]}',
            'breed/affenpinscher' => '{"status":"success","message":{"name":"Affenpinscher","info":"Info text."}}',
            'breed/bullterrier/staffordshire' => '{"status":"success","message":{"name":"Staffordshire Bullterrier","info":"Info Text."}}',
        ];

        $this->setResponses($responses);
    }

    private function setResponses(array $responses)
    {
        $this->responses = $responses;

        return $this;
    }

    public function request($method, $uri = '', array $options = [])
    {
        $code = 500;
        $data = '{"status":"unitFail","message":"URI does not exist in MockApi.php"}';

        foreach ($this->responses as $key => $message) {
            if (substr((string) $uri, (strlen($key) * -1)) == $key) {
                $code = ((strpos($message, 'DOESNOTEXIST') !== false) ? 404 : 200);
                $data = $message;
            }
        }

        $response = new \GuzzleHttp\Psr7\Response($code, ['Content-Type' => 'application/json'], $data);

        return $response;
    }
}
