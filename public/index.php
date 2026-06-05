<?php

declare(strict_types=1);

date_default_timezone_set('Europe/Berlin');

use App\Kernel;

require_once \dirname(__DIR__).'/vendor/autoload_runtime.php';

return static function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
