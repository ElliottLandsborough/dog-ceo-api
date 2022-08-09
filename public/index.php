<?php

use App\Kernel;
use Symfony\Component\ErrorHandler\Debug;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    Debug::enable();
}

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};