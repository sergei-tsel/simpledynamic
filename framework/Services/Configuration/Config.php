<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Configuration;

/**
 * Конфигурация
 * 
 * @psalm-suppress UnusedClass
 */
abstract class Config
{
    /**
     * @var array<string, array<array-key, mixed>|scalar|null>
     */
    protected static array $local     = [];

    /**
     * @var array<string, array<array-key, mixed>|scalar|null>
     */
    protected static array $cache     = [];

    protected static string $filename = '';

    /**
     * Получить конфигурацию
     *
     * @return array<string, array<array-key, mixed>|scalar|null>
     */
    public static function getConfig(): array
    {
        if (static::$cache !== []) {
            return static::$cache;
        }

        if (static::$filename !== '') {
            $parsedYaml = yaml_parse_file(static::$filename);

            if (!is_array($parsedYaml)) {
                static::$cache = static::$local;
                return static::$cache;
            }

            $filteredYaml = array_filter(
                $parsedYaml,
                fn ($value): bool => is_array($value) || is_scalar($value) || $value === null
            );

            $stringKeysYaml = [];
            foreach ($filteredYaml as $key => $value) {
                $stringKeysYaml[(string)$key] = $value;
            }

            static::$cache = array_merge(static::$local, $stringKeysYaml);
            return static::$cache;
        }

        static::$cache = static::$local;

        return static::$cache;
    }

    /**
     * Получить часть конфигурации
     *
     * @psalm-suppress PossiblyUnusedMethod
     * @return array|scalar|null
     */
    public static function getConfigPart(string $name): array|string|int|float|bool|null
    {
        $config = static::getConfig();

        if ($config === [] || !array_key_exists($name, $config)) {
            return null;
        }

        return $config[$name];
    }

    /**
     * Установить конфигурацию
     * 
     * @psalm-suppress PossiblyUnusedMethod
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
     *
     * @psalm-suppress PossiblyUnusedMethod
     * @param array<string, callable> $parts
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
