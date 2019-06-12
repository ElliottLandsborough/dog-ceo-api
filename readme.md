# Dog CEO API

[![Build Status](https://travis-ci.org/ElliottLandsborough/dog-ceo-api.svg?branch=master)](https://travis-ci.org/ElliottLandsborough/dog-ceo-api)
[![Code Style](https://github.styleci.io/repos/97956282/shield?branch=master)](https://github.styleci.io/repos/97956282)

## Info

 - To add your own images submit a pull request to https://github.com/jigsawpieces/dog-api-images
 - Rewritten in Symfony 4 recently. Check out the 'legacy' branch for the old version
 - API requests are cached from lambda https://github.com/ElliottLandsborough/dog-ceo-api-node

## Stats

![Screenshot of statistics page](https://dog.ceo/api/stats.png)

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
