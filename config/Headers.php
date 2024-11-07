<?php
declare(strict_types=1);
namespace config;

/**
 * Конфигурация заголовков
 */
class Headers extends Config
{
    protected static array  $local    = [];
    protected static string $filename = '';

    /**
     * Установить заголовки
     */
    public static function setHeaders(): void
    {
        self::setConfig(function (string|int $key, string|array $value) {
            if (is_string($value)) {
                is_string($key)
                    ? header($key . ': ' . $value)
                    : header($value);
            } elseif (is_array($value)) {
                header(...$value);
            }
        });
    }
}
