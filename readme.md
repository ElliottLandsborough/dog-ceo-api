# Dog CEO API

## Requirements

 - php
 - php-yaml
 - composer
 - http://vision.stanford.edu/aditya86/ImageNetDogs/images.tar
 - phpunit for unit tests

## Setup
 - Must be inside /api folder e.g http://localhost:8000/api
 - Images go into /api/img (e.g /api/img/spaniel-irish and /api/img/spaniel-cocker or /api/img/spaniel)
 - Don't forget to run 'composer install' inside api dir
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

#### /breed/{breed}/images
Get all breed images.

#### /breed/{breed}/images/random
Get random image from a breed (and all its sub-breeds).

#### /breed/{breed}/{breed2}/images
Get all images from sub breed.

#### /breed/{breed}/{breed2}/images/random
Get random image from sub breed.

## Stats (optional)
```
cp .env.example .env
```

```
CREATE DATABASE `dogstats`;

CREATE TABLE `daily` (
    `id` int(11) NOT NULL primary key,
    `route` varchar(100),
    `date` date NOT NULL,
    `hits` int(11) NOT NULL DEFAULT '0'
) ENGINE = InnoDB;

ALTER TABLE `daily`
    ADD KEY `route` (`route`),
    ADD KEY `date` (`date`);

ALTER TABLE `daily`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;COMMIT;
```

## MIT License

Copyright (c) 2017 Dog CEO

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