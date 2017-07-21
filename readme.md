# Dog CEO API

## Requirements

 - php
 - php-yaml
 - composer
 - http://vision.stanford.edu/aditya86/ImageNetDogs/images.tar
 - phpunit for unit tests

## Setup
 - Must be inside /api folder e.g http://localhost:8000/api
 - Images go into /api/img
 - Don't forget to run 'composer install' inside api dir

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