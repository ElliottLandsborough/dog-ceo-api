<?php

// src/Util/MockApi.php

namespace App\Util;

use GuzzleHttp\Exception\ClientException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

/**
 * A mock api - returns a small subset of what lambda does.
 */
class MockApi implements ClientInterface
{
    /** @var array $responses The responses */
    protected array $responses;

    /**
     * Constructor.
     */
    public function __construct()
    {
        // endpoints called by the unit tests
        $responses = [
            'breeds/list/all'                        => '{"status":"success","message":{"affenpinscher":[],"bullterrier":["staffordshire"]}}',
            'breeds/list'                            => '{"status":"success","message":["affenpinscher","bullterrier"]}',
            'breed/affenpinscher/list'               => '{"status":"success","message":[]}',
            'breed/bullterrier/list'                 => '{"status":"success","message":["staffordshire"]}',
            'breed/affenpinscher/images'             => '{"status":"success","message":["https://images.dog.ceo/breeds/affenpinscher/image.jpg"]}',
            'breed/bullterrier/staffordshire/images' => '{"status":"success","message":["https://images.dog.ceo/breeds/bullterrier-staffordshire/image.jpg"]}',
            'breed/affenpinscher'                    => '{"status":"success","message":{"name":"Affenpinscher","info":"Info text."}}',
            'breed/bullterrier/staffordshire'        => '{"status":"success","message":{"name":"Staffordshire Bullterrier","info":"Info text."}}',
        ];
        $this->setResponses($responses);
    }

    /**
     * PSR-18 sendRequest implementation (not used in tests, but required for interface).
     */
    public function sendRequest(RequestInterface $request): \Psr\Http\Message\ResponseInterface
    {
        // Use the request() method for compatibility with BreedUtil
        return $this->request($request->getMethod(), $request->getUri()->__toString());
    }

    /**
     * Sets the responses.
     *
     * @param array $responses
     *
     * @return MockApi $this
     */
    private function setResponses(array $responses): ?self
    {
        $this->responses = $responses;

        return $this;
    }

    /**
     * Override the guzzle request function.
     *
     * @param string $method  The method
     * @param string $uri     The url being requested
     * @param array  $options Options
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function request(string $method, $uri = '', array $options = []): \Psr\Http\Message\ResponseInterface
    {
        // see if we requested an exception
        if ($uri === 'ClientException') {
            throw new ClientException('ClientException', new \GuzzleHttp\Psr7\Request('GET', 'https://domain.test'), new \GuzzleHttp\Psr7\Response(418, [], ''));
        }

        // default to 500/error
        $code = 500;
        $data = '{"status":"unitFail","message":"URI does not exist in MockApi.php"}';

        // loop through responses
        foreach ($this->responses as $key => $message) {
            // did the end of the url match one of them?
            if (substr((string) $uri, strlen($key) * -1) == $key) {
                // set up some vars
                $code = ((strpos($message, 'DOESNOTEXIST') !== false) ? 404 : 200);
                $data = $message;

                // end the foreach, we found a match
                break;
            }
        }

        $response = new \GuzzleHttp\Psr7\Response($code, ['Content-Type' => 'application/json'], $data);

        return $response;
    }
}
