<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * Debug: Log when Kernel is loaded and from where.
     */
    public static function debugLoadedFrom()
    {
        if (true) {
            error_log('App\\Kernel loaded from: ' . __FILE__ . ' (realpath: ' . realpath(__FILE__) . ')');
            error_log("Backtrace:\n" . print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), true));
        }
    }

    // Static property triggers debug output on file load
    public static $debugInit;
}

// Trigger debug output when file is loaded
\App\Kernel::$debugInit = \App\Kernel::debugLoadedFrom();
