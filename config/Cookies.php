<?php
declare(strict_types=1);
namespace config;

/**
 * Конфигурация куки
 */
class Cookies extends Config
{
    protected static array  $local    = [];
    protected static string $filename = '';

    /**
     * Установить куки
     */
    public static function setCookies(): void
    {
        self::setConfig(function (string|int $key, string|array $value) {
            if (is_string($value)) {
                setcookie($key, $value);
            } elseif (is_array($value)) {
                setcookie(...$value);
            }
        });
    }
}
