# Dog CEO API

[![Build Status](https://travis-ci.org/ElliottLandsborough/dog-ceo-api.svg?branch=master)](https://travis-ci.org/ElliottLandsborough/dog-ceo-api)

## Info

 - To add your own images submit a pull request to https://github.com/jigsawpieces/dog-api-images
 - API requests are cached from lambda https://github.com/ElliottLandsborough/dog-ceo-api-node

## Requirements

 - php
 - php-yaml
 - composer
 - http://vision.stanford.edu/aditya86/ImageNetDogs/images.tar
 - run 'vendor/bin/phpunit' for unit tests

## Setup
 - Clone repo
 - cd repo dir
 - composer install
 - Images go into /img (e.g /api/img/spaniel-irish and /api/img/spaniel-cocker or /api/img/spaniel)
 - php -S localhost:8000

## Endpoints

#### /breeds/list/all
List all breed names including sub breeds.

#### /breeds/list
List all master breed names.

#### /breed/{breed}/list
List sub breeds.

#### /breed/{breed}
Get master breed info (data is incomplete, see content folder).

#### /breed/{breed}/{breed2}
Get sub breed info (data is incomplete, see content folder).

#### /breeds/image/random
Random image from any breed.

#### /breeds/image/random/3
Get 3 random images from any breed (max. 50)

#### /breed/{breed}/images
Get all breed images.

#### /breed/{breed}/images/random
Get random image from a breed (and all its sub-breeds).

#### /breed/{breed}/images/random/4
Get 4 random images from a breed (and all its sub-breeds).

#### /breed/{breed}/{breed2}/images
Get all images from a sub breed.

#### /breed/{breed}/{breed2}/images/random
Get random image from a sub breed.

#### /breed/{breed}/{breed2}/images/random/5
Get 5 random images from a sub breed.

## Alt tags (beta)
These endpoints might change in the future...
```
https://dog.ceo/api/breeds/image/random/alt
https://dog.ceo/api/breeds/image/random/3/alt

https://dog.ceo/api/breed/hound/images/alt
https://dog.ceo/api/breed/hound/images/random/alt
https://dog.ceo/api/breed/hound/images/random/3/alt

https://dog.ceo/api/breed/hound/afghan/images/alt
https://dog.ceo/api/breed/hound/afghan/images/random/alt
```

## Stats (optional)
```
cp .env.example .env
```

```
CREATE DATABASE `dogstats`;

CREATE TABLE `dogstats`.`daily` ( `id` INT NOT NULL AUTO_INCREMENT , `route` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL , `date` DATE NOT NULL , `hits` INT NOT NULL DEFAULT '0' , PRIMARY KEY (`id`), INDEX (`route`), INDEX (`date`)) ENGINE = InnoDB;

CREATE TABLE `visits` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `ip` varchar(39) COLLATE 'utf8_unicode_ci' NULL,
  `date` datetime NOT NULL,
  `country` varchar(2) COLLATE 'utf8_unicode_ci' NULL,
  `endpoint` text COLLATE 'utf8_unicode_ci' NULL,
  `user-agent` varchar(255) COLLATE 'utf8_unicode_ci' NULL,
  `referrer` text COLLATE 'utf8_unicode_ci' NULL
);

ALTER TABLE `visits` ADD INDEX(`country`);
ALTER TABLE `daily` ADD INDEX( `route`, `date`);
ALTER TABLE `visits` ADD INDEX(`ip`);
```

Put this at the bottom of index.php
```
use models\Statistic;

// keep some stats after the response is sent
// only do stats if db exists in .env
if (isset($request) && getenv('DOG_CEO_DB_HOST')) {
    //$routeName = $request->get('_route');
    $uri = $request->getRequestUri();
    // only save stats if successful request
    if ($uri !== '/stats' && $response->getStatusCode() == '200') {
        $stats = new Statistic();
        $stats->save($uri);
    }
}
```

## MIT License

Copyright (c) 2018 Dog CEO

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
