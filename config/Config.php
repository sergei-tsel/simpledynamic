<?php

declare(strict_types=1);

namespace config;

/**
 * Базовая конфигурация
 */
class Config
{
    protected static array $local     = [];

    protected static array $cache     = [];

    protected static string $filename = '';


    /**
     * Получить конфигурацию
     */
    public static function getConfig(): array
    {
        if (static::$filename !== '') {
            static::$cache = array_merge(static::$local, yaml_parse_file(static::$filename));
        }

        static::$cache = static::$local;

        return static::$cache;
    }

    /**
     * Получить часть конфигурации
     */
    public static function getConfigPart(string $name): mixed
    {
        $config = static::getConfig();

        if ($config === [] || !array_key_exists($name, $config)) {
            return null;
        }

        return $config[$name];
    }

    /**
     * Установить конфигурацию
     */
    protected static function setConfig(
        callable $setter,
    ): void {
        $local = static::getConfig();

        if ($local === []) {
            return;
        }

        $setter($local);
    }

    /**
     * Установить часть конфигурации
     */
    protected static function setConfigParts(array $parts): void
    {
        $local = static::getConfig();

        if ($local === []) {
            return;
        }

        foreach ($parts as $name => $setter) {
            if (array_key_exists($name, $local)) {
                $setter($local[$name]);
            }
        }
    }
}
