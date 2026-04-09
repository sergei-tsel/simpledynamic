<?php

declare(strict_types=1);

namespace config;

/**
 * Конфигурация приложения
 */
/**
 * Конфигурация приложения
 *
 * @psalm-suppress PossiblyUnusedProperty
 */
class App extends Config
{
    /**
     * @psalm-suppress InvalidAttribute
     */
    #[\Override]
    protected static array $local     = [
        'locale'          => 'en',
        'commands'        => [
            'test' => [],
        ],
        'providers'       => [
            \Sympledynamic\Providers\AppServiceProvider::class,
        ],
        'twig_extensions' => [],
    ];

    /**
     * @psalm-suppress InvalidAttribute
     */
    #[\Override]
    protected static string $filename = '';
}
