<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/assets',
        __DIR__ . '/config',
        __DIR__ . '/public',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPhpSets(php82: true)
    ->withComposerBased(symfony: true)
    ->withTypeCoverageLevel(0)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
    )
;
