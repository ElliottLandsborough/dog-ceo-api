<?php

use MicroKernel;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

// This is why you don't host in subdirectories with apache...
class Kernel extends MicroKernel
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
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
