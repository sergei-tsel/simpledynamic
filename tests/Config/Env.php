<?php

declare(strict_types=1);

namespace Test\Config;

use Simpledynamic\Services\Configuration\Config;

/**
 * Конфигурация переменных среды
 */
class Env extends Config
{
    /**
     * @var array<string, array<array-key, mixed>|scalar|null>
     */
    #[\Override]
    protected static array $local = [];

    #[\Override]
    protected static string $filename = '';

    /**
     * Установить переменные среды
     */
    public static function set(): void
    {
        self::setConfig(static function (array $config): void {
            /** @var array|scalar $value */
            foreach ($config as $key => $value) {
                if (!is_string($value)) {
                    continue;
                }

                putenv($key . '=' . $value);
            }
        });
    }
}
