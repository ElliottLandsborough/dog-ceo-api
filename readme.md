# Dog CEO API

[![codecov](https://codecov.io/gh/ElliottLandsborough/dog-ceo-api/graph/badge.svg?token=wEfVTxeFOz)](https://codecov.io/gh/ElliottLandsborough/dog-ceo-api)
[![CircleCI](https://circleci.com/gh/ElliottLandsborough/dog-ceo-api.svg?style=svg)](https://circleci.com/gh/ElliottLandsborough/dog-ceo-api)
[![Code Style](https://github.styleci.io/repos/97956282/shield?style=flat&branch=main)](https://github.styleci.io/repos/97956282)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/28e7bd35f2fe4d42a19aec5f705c5024)](https://app.codacy.com/gh/ElliottLandsborough/dog-ceo-api/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

## Info

- Images are hosted on [Vultr, The Everywhere Cloud](https://www.vultr.com/?ref=8302423)
- To add your own images submit a pull request to https://github.com/jigsawpieces/dog-api-images
- Some API requests are cached from AWS lambda https://github.com/ElliottLandsborough/dog-ceo-api-golang


## Examples

- p5.js: https://editor.p5js.org/codingtrain/sketches/lQxT7PTKC
- Vanilla JS: https://codepen.io/elliottlan/pen/MNEWNx
- Jquery: https://codepen.io/elliottlan/pen/KOXKLG
- Flutter: https://github.com/LIVELUCKY/dogs
- Node.js: https://github.com/mrbrunelli/dog-time-decorator

## Stats

![Screenshot of statistics page](https://github.com/ElliottLandsborough/dog-ceo-api/blob/main/stats.png?raw=true)

## Dependencies

- php 8.3+
- Symfony 6
- modules
- composer
- run `make build; make install; make test;` for unit tests

Not sure that the yaml module is actually require below:

```bash
$ composer check-platform-reqs
Checking platform requirements for packages in the vendor dir
composer-plugin-api  2.3.0     success                                       
composer-runtime-api 2.2.2     success                                       
ext-ctype            8.1.12    success                                       
ext-dom              20031129  success                                       
ext-filter           8.1.12    success                                       
ext-iconv            8.1.12    success                                       
ext-json             8.1.12    success                                       
ext-libxml           8.1.12    success                                       
ext-mbstring         *         success provided by symfony/polyfill-mbstring 
ext-phar             8.1.12    success                                       
ext-tokenizer        8.1.12    success                                       
ext-xml              8.1.12    success                                       
ext-xmlwriter        8.1.12    success                                       
ext-yaml             2.2.2     success                                       
php                  8.1.12    success
```

## Setup

- Clone repo
- composer install
- cd public
- php -S 127.0.0.1:6969

## .env.local

```
DOG_CEO_CACHE_KEY="something-really-secure"
DOG_CEO_LAMBDA_URI=https://example.execute-api.us-east-1.amazonaws.com/dev/
```

## Cache clear:

```
$ curl -X GET http://127.0.0.1:8000/cache-clear -H 'auth-key: something-really-secure'
```

## Response Structure

All endpoints return JSON in this format:

```json
{
  "message": "...",
  "status": "success"
}
```

The `message` field contains the actual data and varies by endpoint:
- String: single image URL
- Array: lists of breeds, sub-breeds, or multiple image URLs
- Object: breeds with their sub-breeds (key-value pairs)

Error responses include a `code` field:

```json
{
  "status": "error",
  "message": "Breed not found (main breed does not exist)",
  "code": 404
}
```

## Endpoints

### Breed Lists

#### /breeds/list/all

List all breed names including sub breeds.

Returns object with breed names as keys and sub-breed arrays as values. Empty arrays indicate no sub-breeds exist for that breed.

```json
{
  "message": {
    "affenpinscher": [],
    "bulldog": ["boston", "english", "french"],
    "hound": ["afghan", "basset", "blood", "english", "ibizan", "plott", "walker"],
    // ... 98 breeds total
  },
  "status": "success"
}
```

#### /breeds/list/all/random

Get random breed including any sub breeds.

#### /breeds/list/all/random/10

Get 10 random breeds including any sub breeds.

#### /breeds/list

List all main breed names.

Returns simple array of breed names (excludes sub-breed information).

#### /breeds/list/random

Get single random main breed.

#### /breeds/list/random/10

Get 10 random main breeds.

### Sub-Breed Operations

#### /breed/{breed}/list

List sub breeds for a specific breed.

Returns array of sub-breed names without the main breed prefix. Returns empty array if breed has no sub-breeds.

#### /breed/{breed}/list/random

List random sub breed.

#### /breed/{breed}/list/random/10

List 10 random sub breeds.

### Breed Information

#### /breed/{breed}

Get main breed info (data is incomplete, see content folder).

Note: Most breeds return 404 with message "No info file for this breed exists". This is a known limitation.

#### /breed/{breed}/{breed2}

Get sub breed info (data is incomplete, see content folder).

Note: Most sub-breeds return 404 with message "No info file for this breed exists". This is a known limitation.

### Random Images

#### /breeds/image/random

Random image from any breed.

#### /breeds/image/random/3

Get 3 random images from any breed (max. 50)

Returns array of image URLs. **Important**: Requests exceeding 50 images are silently capped at 50 (no error returned). Invalid numbers (non-numeric, zero, negative) default to 1 image.

### Breed Images

#### /breed/{breed}/images

Get all breed images.

**Important**: Returns images from the main breed AND all its sub-breeds combined. For example, `/breed/hound/images` returns 808 images from all hound sub-breeds (afghan, basset, blood, english, ibizan, plott, walker). No pagination is applied.

#### /breed/{breed}/images/random

Get random image from a breed (and all its sub-breeds).

#### /breed/{breed}/images/random/4

Get 4 random images from a breed (and all its sub-breeds).

### Sub-Breed Images

#### /breed/{breed}/{breed2}/images

Get all images from a sub breed.

#### /breed/{breed}/{breed2}/images/random

Get random image from a sub breed.

#### /breed/{breed}/{breed2}/images/random/5

Get 5 random images from a sub breed.

## Understanding Sub-Breeds

Sub-breeds are variations within a main breed. Key behaviors:

- `/breed/{breed}/list` - Returns sub-breed names as array: `["afghan", "basset", ...]`
- `/breed/{breed}/images` - Returns ALL images including all sub-breeds (aggregated)
- `/breed/{breed}/{sub-breed}/images` - Returns only that specific sub-breed's images

Sub-breed names in responses do not include the main breed prefix, but image URLs use hyphenated format: `hound-afghan`.

Example breeds with sub-breeds:
- `bulldog`: boston, english, french
- `hound`: afghan, basset, blood, english, ibizan, plott, walker
- `terrier`: 25 sub-breeds including american, border, scottish, yorkshire

## Image URLs

All image URLs follow this pattern:
```
https://images.dog.ceo/breeds/{breed-subbreed}/{filename}.jpg
```

- Direct CDN links (no authentication required)
- All images are .jpg format
- Can be used directly in HTML `<img>` tags
- Example: `https://images.dog.ceo/breeds/hound-afghan/n02088094_1003.jpg`

## Error Handling

The API returns different error messages for different scenarios:

**Invalid breed name** (404):
```json
{
  "status": "error",
  "message": "Breed not found (main breed does not exist)",
  "code": 404
}
```

**Invalid sub-breed for valid breed** (404):
```json
{
  "status": "error",
  "message": "Breed not found (no sub breeds exist for this main breed)",
  "code": 404
}
```

**Missing breed info files** (404):
```json
{
  "status": "error",
  "message": "Breed not found (No info file for this breed exists)",
  "code": 404
}
```

**Invalid route/typo** (404):
```json
{
  "status": "error",
  "message": "No route found for \"GET http://dog.ceo/api/...\""",
  "code": 404
}
```

### Important Notes

- **Breed names are case-sensitive**: Use lowercase only. `hound` works, `Hound` returns 404.
- **Number parameters are permissive**: Non-numeric values, zero, and negative numbers all default to 1 image (no error).
- **50 image limit is enforced silently**: Requesting 51+ images returns exactly 50 with no error indication.
- **No pagination**: Endpoints like `/breed/{breed}/images` return all images at once (can be hundreds).

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
