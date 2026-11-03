<?php

declare(strict_types=1);

$files = glob(__DIR__.'/var/cache/prod/*.preload.php');

if (false !== $files) {
    foreach ($files as $file) {
        require $file;
    }
}
