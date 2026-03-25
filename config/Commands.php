<?php

declare(strict_types=1);

namespace config;

/**
 * Конфигурация консольных команд
 */
class Commands extends Config
{
    #[\Override]
    protected static array $local     = [
        'test' => [
            \App\Infrastructure\Console\FiberTaskingTest::class,
        ],
    ];
    #[\Override]
    protected static string $filename = '';
}
