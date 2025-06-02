<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require \dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(\dirname(__DIR__).'/.env');
}

// / ### extra actions added ### ///

// executes the "php bin/console cache:clear" command
passthru(\sprintf(
    'APP_ENV=%s php "%s/../bin/console" cache:clear --no-warmup',
    $_ENV['APP_ENV'],
    __DIR__,
));

// Load fixtures
/*
passthru(sprintf(
    'APP_ENV=%s php "%s/../bin/console" doctrine:fixtures:load',
    $_ENV['APP_ENV'],
    __DIR__,
));
*/
