<?php

declare(strict_types=1);

namespace config;

/**
 * Конфигурация переменных среды
 *
 * @psalm-suppress ClassCanBeFinal
 */
class Env extends Config
{
    /**
     * @psalm-suppress InvalidAttribute
     */
    #[\Override]
    protected static array  $local    = [];

    /**
     * @psalm-suppress InvalidAttribute
     */
    #[\Override]
    protected static string $filename = '';

    /**
     * Установить переменные среды
     */
    public static function set(): void
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
