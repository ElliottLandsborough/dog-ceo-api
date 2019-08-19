# Dog CEO API

[![Build Status](https://travis-ci.org/ElliottLandsborough/dog-ceo-api.svg?branch=master)](https://travis-ci.org/ElliottLandsborough/dog-ceo-api)
[![Code Coverage](https://codecov.io/gh/ElliottLandsborough/dog-ceo-api/branch/master/graph/badge.svg)](https://codecov.io/gh/ElliottLandsborough/dog-ceo-api)
[![CircleCI](https://circleci.com/gh/ElliottLandsborough/dog-ceo-api.svg?style=svg)](https://circleci.com/gh/ElliottLandsborough/dog-ceo-api)
[![Code Style](https://github.styleci.io/repos/97956282/shield?style=flat&branch=master)](https://github.styleci.io/repos/97956282)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP from Travis config](https://img.shields.io/travis/php-v/ElliottLandsborough/dog-ceo-api.svg)](https://php.net)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/28e7bd35f2fe4d42a19aec5f705c5024)](https://www.codacy.com/app/ElliottLandsborough/dog-ceo-api?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=ElliottLandsborough/dog-ceo-api&amp;utm_campaign=Badge_Grade)

## Info

 - To add your own images submit a pull request to https://github.com/jigsawpieces/dog-api-images
 - Rewritten in Symfony 4 recently. Check out the 'legacy' branch for the old version
 - API requests are cached from lambda https://github.com/ElliottLandsborough/dog-ceo-api-golang
 
## Examples

 - Vanilla JS: https://codepen.io/elliottlan/pen/MNEWNx
 - Jquery: https://codepen.io/elliottlan/pen/KOXKLG

## Stats

![Screenshot of statistics page](https://github.com/ElliottLandsborough/dog-ceo-api/blob/master/stats.png?raw=true)

## Requirements

 - php 7.3+
 - a few php packages
 - composer
 - run './bin/phpunit' for unit tests

```
$ composer check-platform-reqs
Restricting packages listed in "symfony/symfony" to "4.3.*"
composer-plugin-api
ext-ctype
ext-iconv
ext-tokenizer
ext-xml
php
```

## Setup

 - Clone repo
 - cd repo dir
 - composer install
 - symfony server:start

## .env.local

```
DOG_CEO_CACHE_KEY="something-really-secure-lol"
DOG_CEO_LAMBDA_URI=https://example.execute-api.us-east-1.amazonaws.com/dev/
```

## Cache clear:
```
$ curl -X GET http://127.0.0.1:8000/cache-clear -H 'auth-key: something-really-secure-lol'
```

## Endpoints

#### /breeds/list/all
List all breed names including sub breeds.

#### /breeds/list/all/random
Get random breed including any sub breeds.

#### /breeds/list/all/random/10
Get 10 random breeds including any sub breeds.

#### /breeds/list
List all master breed names.

#### /breeds/list/random
Get single random master breed.

#### /breeds/list/random/10
Get 10 random master breeds.

#### /breed/{breed}/list
List sub breeds.

#### /breed/{breed}/list/random
List random sub breed.

#### /breed/{breed}/list/random/10
List 10 random sub breeds.

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

## Beta/Unfinished Endpoints
These endpoints might change in the future...

### Alt tags (beta)
```
https://dog.ceo/api/breeds/image/random/alt
https://dog.ceo/api/breeds/image/random/1/alt
https://dog.ceo/api/breeds/image/random/9/alt
```
```
https://dog.ceo/api/breed/hound/images/alt
https://dog.ceo/api/breed/hound/images/random/1/alt
https://dog.ceo/api/breed/hound/images/random/9/alt
```
```
https://dog.ceo/api/breed/hound/afghan/images/alt
https://dog.ceo/api/breed/hound/afghan/images/random/alt
```

### XML Responses (beta, unfinished)
Add 'Content-Type' request header containing 'application/xml' to any endpoint.
