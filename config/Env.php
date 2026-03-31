<?php

declare(strict_types=1);

namespace config;

/**
 * Конфигурация переменных среды
 */
class Env extends Config
{
    #[\Override]
    protected static array  $local    = [];

    #[\Override]
    protected static string $filename = '';

    /**
     * Установить переменные среды
     */
    public static function setEnv(): void
    {
        self::setConfig(function (array $config): void {
            foreach ($config as $key => $value) {
                if (is_string($key)) {
                    putenv($key . ':' . $value);
                } else {
                    putenv($value);
                }
            }
        });
    }
}
