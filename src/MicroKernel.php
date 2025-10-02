<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel;

class MicroKernel extends Kernel
{
    use MicroKernelTrait;
}
