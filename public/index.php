<?php

namespace App;

// The main entry point for the application
// see https://symfony.com/doc/current/setup.html#the-front-controller

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

use App\MicroKernel;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;

class Kernel extends MicroKernel
{
    use MicroKernelTrait;

    /**
     * NOTICE:
     *
     * @todo This is a temporary workaround for... something. I forget what.
     *
     * We don't want to deploy composer.json that's being used to
     * compute project dir, so we will specify it here manually.
     */
    public function getProjectDir(): string
    {
        return \dirname(__DIR__);
    }
}

return function (array $context) {
    return new MicroKernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
