<?php

namespace controllers;

use models\Cache;
use Symfony\Component\HttpFoundation\JsonResponse;

class CacheController
{
    // clear all cache file if key is correct
    public function clearAllCacheFiles($key = false)
    {
        $correctKey = getenv('DOG_CEO_CACHE_KEY');
        $submittedKey = $_GET['key'];

        $status = 403;
        $responseArray = (object) ['status' => 'error', 'message' => 'Key was incorrect'];

        if ($correctKey && strlen($correctKey) && strlen($submittedKey) && $correctKey == $submittedKey) {
            $cache = new Cache();
            $cache->clearAll();

            $status = 200;
            $responseArray = (object) ['status' => 'success', 'message' => 'Cache was cleared'];
        }

        $response = new JsonResponse($responseArray, $status);

        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }
}
