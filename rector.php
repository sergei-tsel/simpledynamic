<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/framework',
    ])
    // uncomment to reach your current PHP version
    ->withPhpSets(php85: true)
    ->withTypeCoverageLevel(63);
