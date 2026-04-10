<?php

declare(strict_types=1);

namespace config;

/**
 * Конфигурация заголовков
 *
 * @psalm-suppress ClassCanBeFinal
 */
class Headers extends Config
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
     * Установить заголовки
     */
    public static function set(): void
    {
        self::setConfig(function (array $config): void {
            foreach ($config as $key => $value) {
                if (is_string($value)) {
                    is_string($key)
                        ? header($key . ': ' . $value)
                        : header($value);
                } elseif (is_array($value)) {
                    header(...$value);
                }
            }
        });
    }
}
