<?php

// tests/Util/DefaultControllerTest.php

namespace App\Tests\Util;

use App\Controller\DefaultController;
use App\Util\BreedUtil;
use App\Util\MockApi;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;

// todo: assert error codes
// todo: get all strings etc from mock api and breedutil
class DefaultControllerTest extends WebTestCase
{
    protected $util;
    protected $controller;

    public function setUp(): void
    {
        $this->util = new BreedUtil(new MockApi(), new FilesystemAdapter());
        $this->util->clearCache();

        $this->controller = new DefaultController($this->util, new Request());
    }

    public function testGetAllBreeds(): void
    {
        $r = $this->controller->getAllBreeds();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        /*
        $this->assertEquals('{"status":"success","message":{"affenpinscher":[],"bullterrier":["staffordshire"]}}', $r->getContent());

        $client = static::createClient();

        $client->request(
            Request::METHOD_GET,
            '/url',
            [], // body
            [],
            [
                'HTTP_content-type' => 'application/xml',
            ]
        );

        $request = $client->getRequest();

        $this->controller = new DefaultController($this->util, $request);
        $r = $this->controller->getAllBreeds();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $r);
        $this->assertEquals('<?xml version="1.0"?>
<root><status>success</status><breeds><breed>affenpinscher</breed><breed>bullterrier</breed></breeds><subbreeds><bullterrier>staffordshire</bullterrier></subbreeds><allbreeds><affenpinscher/><bullterrier>staffordshire</bullterrier></allbreeds></root>
', $r->getContent());
        */
    }

    public function testGetAllBreedsRandomSingle(): void
    {
        $r = $this->controller->getAllBreedsRandomSingle();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertCount(1, (array) $message);
    }

    public function testGetAllBreedsRandomMultiple(): void
    {
        // 1 will return 1
        $n = 1;
        $r = $this->controller->getAllBreedsRandomMultiple($n);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertCount($n, (array) $message);

        // 2 will return 2
        $n = 2;
        $r = $this->controller->getAllBreedsRandomMultiple($n);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertCount($n, (array) $message);

        // 3 will return all which is 2
        $n = 3;
        $r = $this->controller->getAllBreedsRandomMultiple($n);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertCount(2, (array) $message);

        // -1 will return 10 (so all which is 2)
        $n = -1;
        $r = $this->controller->getAllBreedsRandomMultiple($n);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertCount(2, (array) $message);

        // (string) will return 1
        $n = '$$';
        $r = $this->controller->getAllBreedsRandomMultiple($n);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertCount(1, (array) $message);
    }

    public function testGetAllTopLevelBreeds(): void
    {
        $r = $this->controller->getAllTopLevelBreeds();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals(['affenpinscher', 'bullterrier'], $message);
    }

    public function testGetAllTopLevelBreedsRandomSingle(): void
    {
        $r = $this->controller->getAllTopLevelBreedsRandomSingle();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertContains($message, ['affenpinscher', 'bullterrier']);
    }

    public function testGetAllTopLevelBreedsRandomMultiple(): void
    {
        $r = $this->controller->getAllTopLevelBreedsRandomMultiple(2);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals($message, ['affenpinscher', 'bullterrier']);

        $r = $this->controller->getAllTopLevelBreedsRandomMultiple(-1);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals($message, ['affenpinscher', 'bullterrier']);

        $r = $this->controller->getAllTopLevelBreedsRandomMultiple(9999);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals($message, ['affenpinscher', 'bullterrier']);

        $r = $this->controller->getAllTopLevelBreedsRandomMultiple('$$');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertContains($message[0], ['affenpinscher', 'bullterrier']);
    }

    public function testGetAllSubBreeds(): void
    {
        $r = $this->controller->getAllSubBreeds('affenpinscher');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertCount(0, (array) $message);

        $r = $this->controller->getAllSubBreeds('bullterrier');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals(['staffordshire'], (array) $message);

        $r = $this->controller->getAllSubBreeds('BAD');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('error', $status);
        $this->assertEquals('Breed not found (main breed does not exist)', $message);
    }

    public function testGetAllSubBreedsRandomSingle(): void
    {
        $r = $this->controller->getAllSubBreedsRandomSingle('bullterrier');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals('staffordshire', $message);

        $r = $this->controller->getAllSubBreedsRandomSingle('BAD');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('error', $status);
        $this->assertEquals('Breed not found (main breed does not exist)', $message);

        $r = $this->controller->getAllSubBreedsRandomSingle('affenpinscher');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('error', $status);
        $this->assertEquals('Breed not found (no sub breeds exist for this main breed)', $message);
    }

    public function testGetAllSubBreedsRandomMulti(): void
    {
        $r = $this->controller->getAllSubBreedsRandomMulti('bullterrier', 2);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals(['staffordshire'], $message);

        $this->setUp();
        $r = $this->controller->getAllSubBreedsRandomMulti('bullterrier', 1);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals(['staffordshire'], $message);

        $this->setUp();
        $r = $this->controller->getAllSubBreedsRandomMulti('bullterrier', 9999);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals(['staffordshire'], $message);

        $this->setUp();
        $r = $this->controller->getAllSubBreedsRandomMulti('bullterrier', -1);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals(['staffordshire'], $message);

        $this->setUp();
        $r = $this->controller->getAllSubBreedsRandomMulti('bad', 1);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('error', $status);
        $this->assertEquals('Breed not found (main breed does not exist)', $message);

        $this->setUp();
        $r = $this->controller->getAllSubBreedsRandomMulti('DOESNOTEXIST', 1);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('error', $status);
        $this->assertEquals('Breed not found (main breed does not exist)', $message);

        $this->setUp();
        $r = $this->controller->getAllSubBreedsRandomMulti('affenpinscher', -1);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('error', $status);
        $this->assertEquals('Breed not found (no sub breeds exist for this main breed)', $message);
    }

    public function testGetTopLevelImages(): void
    {
        $r = $this->controller->getTopLevelImages('affenpinscher');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals(['https://images.dog.ceo/breeds/affenpinscher/image.jpg'], $message);

        $r = $this->controller->getTopLevelImages('DOESNOTEXIST');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('error', $status);
        $this->assertEquals('Breed not found (main breed does not exist)', $message);

        $r = $this->controller->getTopLevelImages('bad');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('error', $status);
        $this->assertEquals('Breed not found (main breed does not exist)', $message);
    }

    public function testGetRandomTopLevelImage(): void
    {
        $r = $this->controller->getRandomTopLevelImage('affenpinscher');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals('https://images.dog.ceo/breeds/affenpinscher/image.jpg', $message);

        $r = $this->controller->getRandomTopLevelImage('DOESNOTEXIST');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('error', $status);
        $this->assertEquals('Breed not found (main breed does not exist)', $message);

        $r = $this->controller->getRandomTopLevelImage('bad');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('error', $status);
        $this->assertEquals('Breed not found (main breed does not exist)', $message);
    }

    public function testGetRandomTopLevelImages(): void
    {
        $r = $this->controller->getRandomTopLevelImages('affenpinscher', 3);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals(['https://images.dog.ceo/breeds/affenpinscher/image.jpg'], $message);

        $r = $this->controller->getRandomTopLevelImages('affenpinscher', 999);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals(['https://images.dog.ceo/breeds/affenpinscher/image.jpg'], $message);

        $r = $this->controller->getRandomTopLevelImages('affenpinscher', -1);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals(['https://images.dog.ceo/breeds/affenpinscher/image.jpg'], $message);

        $r = $this->controller->getRandomTopLevelImages('affenpinscher', 'bad');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals(['https://images.dog.ceo/breeds/affenpinscher/image.jpg'], $message);

        $r = $this->controller->getRandomTopLevelImages('DOESNOTEXIST', 3);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('error', $status);
        $this->assertEquals('Breed not found (main breed does not exist)', $message);

        $r = $this->controller->getRandomTopLevelImages('bad', 3);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('error', $status);
        $this->assertEquals('Breed not found (main breed does not exist)', $message);
    }

    public function testGetRandomSubLevelImage(): void
    {
        $r = $this->controller->getRandomSubLevelImage('bullterrier', 'staffordshire');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals('https://images.dog.ceo/breeds/bullterrier-staffordshire/image.jpg', $message);

        $r = $this->controller->getRandomSubLevelImage('bullterrier', 'DOESNOTEXIST');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('error', $status);
        $this->assertEquals('Breed not found (sub breed does not exist)', $message);

        $r = $this->controller->getRandomSubLevelImage('bad', 'bad');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('error', $status);
        $this->assertEquals('Breed not found (main breed does not exist)', $message);
    }

    public function testGetRandomSubLevelImages(): void
    {
        $r = $this->controller->getRandomSubLevelImages('bullterrier', 'staffordshire', 1);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals(['https://images.dog.ceo/breeds/bullterrier-staffordshire/image.jpg'], $message);

        $r = $this->controller->getRandomSubLevelImages('bullterrier', 'staffordshire', 2);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals(['https://images.dog.ceo/breeds/bullterrier-staffordshire/image.jpg'], $message);

        $r = $this->controller->getRandomSubLevelImages('bullterrier', 'staffordshire', 999);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals(['https://images.dog.ceo/breeds/bullterrier-staffordshire/image.jpg'], $message);

        $r = $this->controller->getRandomSubLevelImages('bullterrier', 'staffordshire', 'bad');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals(['https://images.dog.ceo/breeds/bullterrier-staffordshire/image.jpg'], $message);

        $r = $this->controller->getRandomSubLevelImages('bullterrier', 'DOESNOTEXIST', 3);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('error', $status);
        $this->assertEquals('Breed not found (sub breed does not exist)', $message);

        $r = $this->controller->getRandomSubLevelImages('bad', 'bad', 3);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('error', $status);
        $this->assertEquals('Breed not found (main breed does not exist)', $message);
    }

    public function testGetRandomImage(): void
    {
        $r = $this->controller->getRandomImage();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertContains($message, ['https://images.dog.ceo/breeds/bullterrier-staffordshire/image.jpg', 'https://images.dog.ceo/breeds/affenpinscher/image.jpg']);
    }

    public function testGetRandomImages(): void
    {
        $r = $this->controller->getRandomImages(5);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertCount(5, $message);

        $r = $this->controller->getRandomImages(-1);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertCount(1, $message);

        $r = $this->controller->getRandomImages(99999);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertCount(50, $message);

        $r = $this->controller->getRandomImages('bad');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertCount(1, $message);
    }

    public function testmainText(): void
    {
        $r = $this->controller->mainText('affenpinscher');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = (object) $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals('Affenpinscher', $message->name);
        $this->assertEquals('Info text.', $message->info);

        $r = $this->controller->mainText('DOESNOTEXIST');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('error', $status);
        $this->assertEquals('Breed not found (main breed does not exist)', $message);
    }

    public function testSubText(): void
    {
        $r = $this->controller->subText('bullterrier', 'staffordshire');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = (object) $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals('Staffordshire Bullterrier', $message->name);
        $this->assertEquals('Info text.', $message->info);

        $r = $this->controller->subText('bullterrier', 'DOESNOTEXIST');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('error', $status);
        $this->assertEquals('Breed not found (sub breed does not exist)', $message);

        $r = $this->controller->subText('DOESNOTEXIST', 'DOESNOTEXIST');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('error', $status);
        $this->assertEquals('Breed not found (main breed does not exist)', $message);
    }

    public function testGetTopLevelImagesWithAltTags(): void
    {
        $r = $this->controller->getTopLevelImagesWithAltTags('affenpinscher');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals('https://images.dog.ceo/breeds/affenpinscher/image.jpg', $message[0]->url);
        $this->assertEquals('Affenpinscher dog', $message[0]->altText);

        $r = $this->controller->getTopLevelImagesWithAltTags('DOESNOTEXIST');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('error', $status);
        $this->assertEquals('Breed not found (main breed does not exist)', $message);
    }

    public function testGetRandomTopLevelImagesWithAltTags(): void
    {
        $r = $this->controller->getRandomTopLevelImagesWithAltTags('affenpinscher', 3);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals('https://images.dog.ceo/breeds/affenpinscher/image.jpg', $message[0]->url);
        $this->assertEquals('Affenpinscher dog', $message[0]->altText);

        $r = $this->controller->getRandomTopLevelImagesWithAltTags('affenpinscher', -1);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals('https://images.dog.ceo/breeds/affenpinscher/image.jpg', $message[0]->url);
        $this->assertEquals('Affenpinscher dog', $message[0]->altText);

        $r = $this->controller->getRandomTopLevelImagesWithAltTags('affenpinscher', 9999);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals('https://images.dog.ceo/breeds/affenpinscher/image.jpg', $message[0]->url);
        $this->assertEquals('Affenpinscher dog', $message[0]->altText);

        $r = $this->controller->getRandomTopLevelImagesWithAltTags('DOESNOTEXIST', 3);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('error', $status);
        $this->assertEquals('Breed not found (main breed does not exist)', $message);
    }

    public function testGetSubLevelImages(): void
    {
        $r = $this->controller->getSubLevelImages('bullterrier', 'staffordshire');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals(['https://images.dog.ceo/breeds/bullterrier-staffordshire/image.jpg'], $message);

        $r = $this->controller->getSubLevelImages('bullterrier', 'DOESNOTEXIST');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('error', $status);
        $this->assertEquals('Breed not found (sub breed does not exist)', $message);

        $r = $this->controller->getSubLevelImages('DOESNOTEXIST', 'DOESNOTEXIST');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('error', $status);
        $this->assertEquals('Breed not found (main breed does not exist)', $message);
    }

    public function testGetSubLevelImagesWithAltTags(): void
    {
        $r = $this->controller->getSubLevelImagesWithAltTags('bullterrier', 'staffordshire');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals('https://images.dog.ceo/breeds/bullterrier-staffordshire/image.jpg', $message[0]->url);
        $this->assertEquals('Staffordshire bullterrier dog', $message[0]->altText);

        $r = $this->controller->getSubLevelImagesWithAltTags('bullterrier', 'DOESNOTEXIST');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('error', $status);
        $this->assertEquals('Breed not found (sub breed does not exist)', $message);

        $r = $this->controller->getSubLevelImagesWithAltTags('DOESNOTEXIST', 'DOESNOTEXIST');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('error', $status);
        $this->assertEquals('Breed not found (main breed does not exist)', $message);
    }

    public function testGetRandomSubLevelImagesWithAltTags(): void
    {
        $r = $this->controller->getRandomSubLevelImagesWithAltTags('bullterrier', 'staffordshire', 2);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals('https://images.dog.ceo/breeds/bullterrier-staffordshire/image.jpg', $message[0]->url);
        $this->assertEquals('Staffordshire bullterrier dog', $message[0]->altText);

        $r = $this->controller->getRandomSubLevelImagesWithAltTags('bullterrier', 'staffordshire', -1);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals('https://images.dog.ceo/breeds/bullterrier-staffordshire/image.jpg', $message[0]->url);
        $this->assertEquals('Staffordshire bullterrier dog', $message[0]->altText);

        $r = $this->controller->getRandomSubLevelImagesWithAltTags('bullterrier', 'staffordshire', 9999);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals('https://images.dog.ceo/breeds/bullterrier-staffordshire/image.jpg', $message[0]->url);
        $this->assertEquals('Staffordshire bullterrier dog', $message[0]->altText);

        $r = $this->controller->getRandomSubLevelImagesWithAltTags('bullterrier', 'staffordshire', 'bad');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals('https://images.dog.ceo/breeds/bullterrier-staffordshire/image.jpg', $message[0]->url);
        $this->assertEquals('Staffordshire bullterrier dog', $message[0]->altText);

        $r = $this->controller->getRandomSubLevelImagesWithAltTags('bullterrier', 'DOESNOTEXIST', 2);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('error', $status);
        $this->assertEquals('Breed not found (sub breed does not exist)', $message);

        $r = $this->controller->getRandomSubLevelImagesWithAltTags('DOESNOTEXIST', 'DOESNOTEXIST', 2);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('error', $status);
        $this->assertEquals('Breed not found (main breed does not exist)', $message);
    }

    public function testGetRandomImagesWithAltTags(): void
    {
        $r = $this->controller->getRandomImagesWithAltTags(1);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals(true, isset($message[0]->url));
        $this->assertEquals(true, isset($message[0]->altText));
        $this->assertCount(1, $message);

        $r = $this->controller->getRandomImagesWithAltTags(-1);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals(true, isset($message[0]->url));
        $this->assertEquals(true, isset($message[0]->altText));
        $this->assertCount(1, $message);

        $r = $this->controller->getRandomImagesWithAltTags(9999);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals(true, isset($message[0]->url));
        $this->assertEquals(true, isset($message[0]->altText));
        $this->assertCount(50, $message);

        $r = $this->controller->getRandomImagesWithAltTags('bad');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals(true, isset($message[0]->url));
        $this->assertEquals(true, isset($message[0]->altText));
        $this->assertCount(1, $message);
    }

    public function testCacheClear(): void
    {
        $r = $this->controller->cacheClear();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals('Cache was not cleared', $message);

        $client = static::createClient();

        if (!isset($_ENV['DOG_CEO_CACHE_KEY'])) {
            exit('Cache key was not set for some reason?');
        }

        $_SERVER['HTTP_auth-key'] = $_ENV['DOG_CEO_CACHE_KEY'];

        $client->request(
            Request::METHOD_GET,
            '/url',
            [], // body
            [],
            [
                //'HTTP_auth-key' => $_ENV['DOG_CEO_CACHE_KEY'],
            ]
        );

        $request = $client->getRequest();

        $this->controller = new DefaultController($this->util, $request);

        $r = $this->controller->cacheClear();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $json = $r->getContent();
        $object = json_decode($json);
        $message = $object->message;
        $status = $object->status;
        $this->assertEquals('success', $status);
        $this->assertEquals('Success, cache was cleared with key', $message);
    }
}
