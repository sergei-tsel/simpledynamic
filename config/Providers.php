<?php

declare(strict_types=1);

namespace config;

/**
 * Конфигурация провайдеров
 */
class Providers extends Config
{
    protected static array $local     = [
        'App\Framework\Providers\AppServiceProvider',
    ];
    protected static string $filename = '';
}
