<?php

declare(strict_types=1);

namespace config;

/**
 * Конфигурация переменных среды
 *
 * @api
 * @psalm-suppress ClassCanBeFinal
 */
class Env extends Config
{
    /**
     * @var array<string, array<array-key, mixed>|scalar|null>
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
        self::setConfig(
            function (array $config): void {
                /** @var array|scalar $value */
                foreach ($config as $key => $value) {
                    if (is_string($value)) {
                        putenv($key . '=' . $value);
                    }
                }
            }
        );
    }
}
