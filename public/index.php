<?php

use App\Kernel as BaseKernel;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

// This is why you don't host in subdirectories with apache...
class DogKernel1337 extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * NOTICE:
     * We don't want to deploy composer.json that's being used to compute project dir, so we will specify it here manually.
     */
    public function getProjectDir(): string
    {
        return \dirname(__DIR__);
    }
}

return function (array $context) {
    return new DogKernel1337($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
