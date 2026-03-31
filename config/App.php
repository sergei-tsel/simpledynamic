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
            'test' => [
                \App\Infrastructure\Console\FiberTaskingTest::class,
            ],
        ],
        'providers'       => [
            \App\Framework\Providers\AppServiceProvider::class,
        ],
        'twig_extensions' => [],
    ];

    #[\Override]
    protected static string $filename = '';
}
