<?php

declare(strict_types=1);

namespace config;

/**
 * Конфигурация приложения
 */
class App extends Config
{
    #[\Override]
    protected static array $local     = [
        'locale'          => 'en',
        'commands'        => [
            'test' => [],
        ],
        'providers'       => [
            \Framework\Providers\AppServiceProvider::class,
        ],
        'twig_extensions' => [],
    ];

    #[\Override]
    protected static string $filename = '';
}
