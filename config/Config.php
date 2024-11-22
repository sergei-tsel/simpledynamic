<?php

declare(strict_types=1);

namespace config;

/**
 * Базовая конфигурация
 */
class Config
{
    protected static array $local     = [];
    protected static string $filename = '';

    /**
     * Получить конфигурацию
     */
    public static function getConfig(): array
    {
        if (static::$filename) {
            return array_merge(
                static::$local,
                yaml_parse_file(static::$filename),
            );
        }

        return static::$local;
    }

    /**
     * Установить конфигурацию
     */
    protected static function setConfig(
        callable $firstMethod,
        array $parts = [],
        array $groups = [],
    ): void {
        $local = static::getConfig();

        foreach ($local as $key => $value) {
            if (! array_key_exists($key, $parts)) {
                $firstMethod($key, $value);
            }
        }

        foreach ($parts as $part => $method) {
            if (in_array($part, $groups)) {
                $method($local[$part]);
            } else {
                foreach ($local[$part] as $key => $value) {
                    $method($key, $value);
                }
            }
        }
    }
}
