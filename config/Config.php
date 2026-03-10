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
        callable $setter,
        array $parts = [],
        array $groups = [],
    ): void {
        $local = static::getConfig();

        foreach ($local as $key => $value) {
            if (!array_key_exists($key, $parts)) {
                $setter($key, $value);
            }
        }

        if ($parts === []) {
            return;
        }

        foreach ($parts as $partName => $partSetter) {
            if (!array_key_exists($partName, $local)) {
                continue;
            }

            if (in_array($partName, $groups)) {
                $partSetter($local[$partName]);
            } elseif(is_array($local[$partName])) {
                foreach ($local[$partName] as $key => $value) {
                    $partSetter($key, $value);
                }
            }
        }
    }
}
