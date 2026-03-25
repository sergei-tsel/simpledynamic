<?php

declare(strict_types=1);

namespace config;

/**
 * Конфигурация провайдеров
 */
class Providers extends Config
{
    #[\Override]
    protected static array $local     = [
        \App\Framework\Providers\AppServiceProvider::class,
    ];
    #[\Override]
    protected static string $filename = '';
}
