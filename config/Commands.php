<?php

declare(strict_types=1);

namespace config;

/**
 * Конфигурация консольных команд
 */
class Commands extends Config
{
    protected static array $local     = [
        'test' => [
            'App\Infrastructure\Console\FiberTaskingTest',
        ],
    ];
    protected static string $filename = '';
}
