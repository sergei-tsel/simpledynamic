<?php
declare(strict_types=1);
namespace config;

/**
 * Конфигурация приложения
 */
class App extends Config
{
    protected static array $local     = [
        'locale' => 'en',
    ];
    protected static string $filename = '';
}
