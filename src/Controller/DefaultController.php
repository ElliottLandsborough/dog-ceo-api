<?php

namespace App\Controller;

use App\Util\BreedUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    protected $breedUtil;
    protected $cacheKey;
    protected $request;

    /**
     * @param BreedUtil $breedUtil
     */
    public function __construct(BreedUtil $breedUtil, Request $request)
    {
        $this->request = $request;

        $this->cacheKey = isset($_ENV['DOG_CEO_CACHE_KEY']) ? $_ENV['DOG_CEO_CACHE_KEY'] : false;

        $endpointUrl = isset($_ENV['DOG_CEO_LAMBDA_URI']) ? $_ENV['DOG_CEO_LAMBDA_URI'] : '';

        // Hint: uncomment to debug environment variables
        //echo "WARNING: Debugging environment variables." . PHP_EOL;
        //var_dump($_ENV); exit(1);

        $this->breedUtil = $breedUtil->setEndpointUrl($endpointUrl);

        // enable XML output if the header is set
        if ($request->headers->get('content-type') === 'application/xml') {
            $this->breedUtil->xmlOutputEnable();
        }
    }

    #[Route('/breeds/list/all', methods: ['GET', 'HEAD'])]
    #[Route('/api/breeds/list/all', methods: ['GET', 'HEAD'])]
    public function getAllBreeds(): ?object
    {
        return $this->breedUtil->getAllBreeds()->getResponseWithCacheHeaders();
    }

    #[Route('/breeds/list/all/random', methods: ['GET', 'HEAD'])]
    #[Route('/api/breeds/list/all/random', methods: ['GET', 'HEAD'])]
    public function getAllBreedsRandomSingle(): ?object
    {
        return $this->breedUtil->getAllBreedsRandomSingle()->getResponse();
    }

    #[Route('/breeds/list/all/random/{amount}', methods: ['GET', 'HEAD'])]
    #[Route('/api/breeds/list/all/random/{amount}', methods: ['GET', 'HEAD'])]
    public function getAllBreedsRandomMultiple(string $amount): ?object
    {
        $amount = (int) $amount ?: 1;

        return $this->breedUtil->getAllBreedsRandomMultiple($amount)->getResponse();
    }

    #[Route('/breeds/list', methods: ['GET', 'HEAD'])]
    #[Route('/api/breeds/list', methods: ['GET', 'HEAD'])]
    public function getAllTopLevelBreeds(): ?object
    {
        return $this->breedUtil->getAllTopLevelBreeds()->getResponseWithCacheHeaders();
    }

    #[Route('/breeds/list/random', methods: ['GET', 'HEAD'])]
    #[Route('/api/breeds/list/random', methods: ['GET', 'HEAD'])]
    public function getAllTopLevelBreedsRandomSingle(): ?object
    {
        return $this->breedUtil->getAllTopLevelBreedsRandomSingle()->getResponse();
    }

    #[Route('/breeds/list/random/{amount}', methods: ['GET', 'HEAD'])]
    #[Route('/api/breeds/list/random/{amount}', methods: ['GET', 'HEAD'])]
    public function getAllTopLevelBreedsRandomMultiple(string $amount): ?object
    {
        $amount = (int) $amount ?: 1;

        return $this->breedUtil->getAllTopLevelBreedsRandomMultiple($amount)->getResponse();
    }

    #[Route('/breed/{breed}/list', methods: ['GET', 'HEAD'])]
    #[Route('/api/breed/{breed}/list', methods: ['GET', 'HEAD'])]
    public function getAllSubBreeds(string $breed): ?object
    {
        return $this->breedUtil->getAllSubBreeds($breed)->getResponseWithCacheHeaders();
    }

    #[Route('/breed/{breed}/list/random', methods: ['GET', 'HEAD'])]
    #[Route('/api/breed/{breed}/list/random', methods: ['GET', 'HEAD'])]
    public function getAllSubBreedsRandomSingle(string $breed): ?object
    {
        return $this->breedUtil->getAllSubBreedsRandomSingle($breed)->getResponse();
    }

    #[Route('/breed/{breed}/list/random/{amount}', methods: ['GET', 'HEAD'])]
    #[Route('/api/breed/{breed}/list/random/{amount}', methods: ['GET', 'HEAD'])]
    public function getAllSubBreedsRandomMulti(string $breed, string $amount): ?object
    {
        $amount = (int) $amount ?: 1;

        return $this->breedUtil->getAllSubBreedsRandomMulti($breed, $amount)->getResponse();
    }

    #[Route('/breed/{breed}/images', methods: ['GET', 'HEAD'])]
    #[Route('/api/breed/{breed}/images', methods: ['GET', 'HEAD'])]
    public function getTopLevelImages(string $breed): ?object
    {
        return $this->breedUtil->getTopLevelImages($breed)->getResponseWithCacheHeaders();
    }

    #[Route('/breed/{breed}/images/random', methods: ['GET', 'HEAD'])]
    #[Route('/api/breed/{breed}/images/random', methods: ['GET', 'HEAD'])]
    public function getRandomTopLevelImage(string $breed): ?object
    {
        return $this->breedUtil->getRandomTopLevelImage($breed)->getResponse();
    }

    #[Route('/breed/{breed}/images/random/{amount}', methods: ['GET', 'HEAD'])]
    #[Route('/api/breed/{breed}/images/random/{amount}', methods: ['GET', 'HEAD'])]
    public function getRandomTopLevelImages(string $breed, string $amount): ?object
    {
        $amount = (int) $amount ?: 1;

        return $this->breedUtil->getRandomTopLevelImages($breed, $amount)->getResponse();
    }

    #[Route('/breed/{breed1}/{breed2}/images', methods: ['GET', 'HEAD'])]
    #[Route('/api/breed/{breed1}/{breed2}/images', methods: ['GET', 'HEAD'])]
    public function getSubLevelImages(string $breed1, string $breed2): ?object
    {
        return $this->breedUtil->getSubLevelImages($breed1, $breed2)->getResponseWithCacheHeaders();
    }

    #[Route('/breed/{breed1}/{breed2}/images/random', methods: ['GET', 'HEAD'])]
    #[Route('/api/breed/{breed1}/{breed2}/images/random', methods: ['GET', 'HEAD'])]
    public function getRandomSubLevelImage(string $breed1, string $breed2): ?object
    {
        return $this->breedUtil->getRandomSubLevelImage($breed1, $breed2)->getResponse();
    }

    #[Route('/breed/{breed1}/{breed2}/images/random/{amount}', methods: ['GET', 'HEAD'])]
    #[Route('/api/breed/{breed1}/{breed2}/images/random/{amount}', methods: ['GET', 'HEAD'])]
    public function getRandomSubLevelImages(string $breed1, string $breed2, string $amount): ?object
    {
        $amount = (int) $amount ?: 1;

        return $this->breedUtil->getRandomSubLevelImages($breed1, $breed2, $amount)->getResponse();
    }

    #[Route('/breeds/image/random', methods: ['GET', 'HEAD'])]
    #[Route('/api/breeds/image/random', methods: ['GET', 'HEAD'])]
    public function getRandomImage(): ?object
    {
        return $this->breedUtil->getRandomImage()->getResponse();
    }

    #[Route('/breeds/image/random/{amount}', methods: ['GET', 'HEAD'])]
    #[Route('/api/breeds/image/random/{amount}', methods: ['GET', 'HEAD'])]
    public function getRandomImages(string $amount): ?object
    {
        $amount = (int) $amount ?: 1;

        return $this->breedUtil->getRandomImages($amount)->getResponse();
    }

    #[Route('/breed/{breed}', methods: ['GET', 'HEAD'])]
    #[Route('/api/breed/{breed}', methods: ['GET', 'HEAD'])]
    public function mainText(string $breed): ?object
    {
        return $this->breedUtil->mainText($breed)->getResponseWithCacheHeaders();
    }

    #[Route('/breed/{breed1}/{breed2}', methods: ['GET', 'HEAD'])]
    #[Route('/api/breed/{breed1}/{breed2}', methods: ['GET', 'HEAD'])]
    public function subText(string $breed1, string $breed2): ?object
    {
        return $this->breedUtil->subText($breed1, $breed2)->getResponseWithCacheHeaders();
    }

    #[Route('/breed/{breed}/images/alt', methods: ['GET', 'HEAD'])]
    #[Route('/api/breed/{breed}/images/alt', methods: ['GET', 'HEAD'])]
    public function getTopLevelImagesWithAltTags(string $breed): ?object
    {
        return $this->breedUtil->getTopLevelImagesWithAltTags($breed)->getResponseWithCacheHeaders();
    }

    #[Route('/breed/{breed}/images/random/{amount}/alt', methods: ['GET', 'HEAD'])]
    #[Route('/api/breed/{breed}/images/random/{amount}/alt', methods: ['GET', 'HEAD'])]
    public function getRandomTopLevelImagesWithAltTags(string $breed, string $amount): ?object
    {
        $amount = (int) $amount ?: 1;

        return $this->breedUtil->getRandomTopLevelImagesWithAltTags($breed, $amount)->getResponse();
    }

    #[Route('/breed/{breed1}/{breed2}/images/alt', methods: ['GET', 'HEAD'])]
    #[Route('/api/breed/{breed1}/{breed2}/images/alt', methods: ['GET', 'HEAD'])]
    public function getSubLevelImagesWithAltTags(string $breed1, string $breed2): ?object
    {
        return $this->breedUtil->getSubLevelImagesWithAltTags($breed1, $breed2)->getResponseWithCacheHeaders();
    }

    #[Route('/breed/{breed1}/{breed2}/images/random/{amount}/alt', methods: ['GET', 'HEAD'])]
    #[Route('/api/breed/{breed1}/{breed2}/images/random/{amount}/alt', methods: ['GET', 'HEAD'])]
    public function getRandomSubLevelImagesWithAltTags(string $breed1, string $breed2, string $amount): ?object
    {
        $amount = (int) $amount ?: 1;

        return $this->breedUtil->getRandomSubLevelImagesWithAltTags($breed1, $breed2, $amount)->getResponse();
    }

    #[Route('/breeds/image/random/{amount}/alt', methods: ['GET', 'HEAD'])]
    #[Route('/api/breeds/image/random/{amount}/alt', methods: ['GET', 'HEAD'])]
    public function getRandomImagesWithAltTags(string $amount): ?object
    {
        $amount = (int) $amount ?: 1;

        return $this->breedUtil->getRandomImagesWithAltTags($amount)->getResponse();
    }

    #[Route('/cache-clear', methods: ['GET', 'HEAD'])]
    #[Route('/api/cache-clear', methods: ['GET', 'HEAD'])]
    public function cacheClear(): ?JsonResponse
    {
        $message = 'Cache was not cleared';

        // the false check means people can't clear the cache unless it is set
        if ($this->cacheKey !== false && $this->request->headers->get('auth-key') === trim($this->cacheKey)) {
            $message = 'Success, cache was cleared with key';
            $this->breedUtil->clearCache();
        }

        $response = new JsonResponse([
            'status'  => 'success',
            'message' => $message,
        ]);

        $response->setStatusCode(200);

        return $response;
    }
}
