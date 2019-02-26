<?php

namespace models;

/**
 * Ridiculously simple caching class.
 */
class Cache
{
    // cache something, cache name, how many minutes to cache, function to use
    public function storeAndReturn($name, $minutes, $closure)
    {
        $path = realpath(__DIR__.'/../cache');
        $name = preg_replace("/[^a-z0-9_.-]+/i", "#", strtolower($name));
        $cacheFile = $path.'/'.$name;
        // if path is writeable
        if (is_writable($path)) {
            // if the cache file does not exist or exists and is older than $minutes
            if (!file_exists($cacheFile) || time() - filemtime($cacheFile) > $minutes * 60) {
                // run the function
                $data = $closure();
                // write the file
                file_put_contents($cacheFile, serialize($data));
            } else {
                // othwerwise just get the file contents
                $data = unserialize(file_get_contents($cacheFile));
            }
        }

        return $data;
    }

    // clear all cache files
    public function clearAll()
    {
        $path = realpath(__DIR__.'/../cache');
        $files = glob($path.'/*');
        foreach ($files as $file) { // iterate files
            if (is_file($file)) {
                unlink($file); // delete file
            }
        }
    }
}
