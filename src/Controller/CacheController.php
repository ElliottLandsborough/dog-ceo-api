<?php

namespace App\Controller;

use App\Util\BreedUtil;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class CacheController extends AbstractController
{
    protected BreedUtil $breedUtil;
    protected ContainerInterface $container;
    protected Request $request;

    protected ?string $cacheKey;

    public function __construct(
        BreedUtil $breedUtil,
        ContainerInterface $container,
        Request $request
    ) {
        // Todo: figure out why injecting Request doesn't work as expected
        $this->breedUtil = $breedUtil;
        $this->container = $container;
        $this->request = $request;

        $this->cacheKey = isset($_ENV['DOG_CEO_CACHE_KEY']) ? trim(substr($_ENV['DOG_CEO_CACHE_KEY'], 0, 128)) : null;
    }

    #[Route('/cache-clear', methods: ['GET', 'HEAD'])]
    #[Route('/api/cache-clear', methods: ['GET', 'HEAD'])]
    public function cacheClear(): ?JsonResponse
    {
        // Get the current request instead of the injected one
        $currentRequest = $this->container->get('request_stack')->getCurrentRequest();

        $successMessage = 'Success, cache was cleared';
        $errorMessage = 'Cache was not cleared';

        $success = false;

        // Check for auth-key header
        if (
            $currentRequest->headers->has('auth-key')
            && $this->cacheKey
            && $this->cacheKey === trim(substr($currentRequest->headers->get('auth-key'), 0, 128))
        ) {
            $this->breedUtil->clearCache();
            $success = true;
        }

        $response = new JsonResponse([
            'status'  => 'success',
            'message' => $success ? $successMessage : $errorMessage,
        ]);

        $response->setStatusCode(200);

        // Pause for 4
        sleep(4);

        return $response;
    }
}
