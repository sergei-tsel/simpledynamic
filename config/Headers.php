<?php

declare(strict_types=1);

namespace config;

/**
 * Конфигурация заголовков
 */
class Headers extends Config
{
    #[\Override]
    protected static array  $local    = [];

    #[\Override]
    protected static string $filename = '';

    /**
     * Установить заголовки
     */
    public static function setHeaders(): void
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
