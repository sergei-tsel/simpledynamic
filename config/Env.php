<?php

declare(strict_types=1);

namespace config;

/**
 * Конфигурация переменных среды
 */
class Env extends Config
{
    protected static array  $local    = [];
    protected static string $filename = '';

    /**
     * Установить переменные среды
     */
    public static function setEnv(): void
    {
        self::setConfig(function (string|int $key, string $value): void {
            if (is_string($key)) {
                putenv($key . ':' . $value);
            } else {
                putenv($value);
            }
        });
    }
}
