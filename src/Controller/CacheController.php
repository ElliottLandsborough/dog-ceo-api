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
        $this->breedUtil = $breedUtil;
        $this->container = $container;
        $this->request = $request;

        $this->cacheKey = $this->sanitizeKey($this->safeEnv('DOG_CEO_CACHE_KEY'));
    }

    // Safely gets an environment variable
    private function safeEnv(string $key): ?string
    {
        $value = $_ENV[$key] ?? getenv($key) ?: null;

        if ($value === false) {
            return null;
        }

        return $value;
    }

    private function sanitizeKey(?string $key): ?string
    {
        if ($key === null || $key === '') {
            return null;
        }

        // Remove any potentially dangerous characters, allow only alphanumeric, hyphens, underscores, and safe punctuation
        $sanitized = preg_replace('/[^a-zA-Z0-9\-_\.!@#$%^&*()+=]/', '', trim($key));

        // Limit length to prevent overflow attacks
        $sanitized = substr($sanitized, 0, 128);

        // Ensure the key has minimum length for security
        if (strlen($sanitized) < 8) {
            return null;
        }

        return $sanitized;
    }

    private function validateAuthKey(?string $providedKey): bool
    {
        if ($providedKey === null) {
            return false;
        }

        // Apply the same sanitization to the provided key
        $sanitizedProvidedKey = $this->sanitizeKey($providedKey);

        if ($sanitizedProvidedKey === null) {
            return false;
        }

        // Use timing-safe comparison to prevent timing attacks
        return hash_equals($this->cacheKey, $sanitizedProvidedKey);
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
            && $this->validateAuthKey($currentRequest->headers->get('auth-key'))
        ) {
            $this->breedUtil->clearCache();
            $success = true;
        }

        $response = new JsonResponse([
            'status'  => 'success',
            'message' => $success ? $successMessage : $errorMessage,
        ]);

        $response->setStatusCode(200);

        //sleep(rand(5, 15));

        return $response;
    }
}
