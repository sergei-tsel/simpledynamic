<?php

declare(strict_types=1);

namespace Test\Config;

use Simpledynamic\Services\Configuration\Config;

/**
 * Конфигурация роутов
 */
class Routes extends Config
{
    /**
     * @var array<string, array<array-key, mixed>|scalar|null>
     */
    #[\Override]
    protected static array $local = [
        'base' => 'http://localhost:8000',
        'default' => 'test',
        'groups' => [
            'test' => \Test\App\Http\Routes\Test::class,
        ],
    ];

    #[\Override]
    protected static string $filename = '';

    /**
     * Получить префикс пути, назначенный ключу base
     */
    public static function getBase(): string
    {
        /** @var string $base */
        $base = self::getConfigPart('base') ?? '';

        return $base === '' ? '/' : $base;
    }

    /**
     * Получить ключ группы роутов по первому фрагменту пути после префикса base
     */
    public static function getGroupKey(string $path): ?string
    {
        /** @var array<string, mixed> $groups */
        $groups = self::getConfigPart('groups') ?? [];

        $segments = explode('/', trim($path, '/'));

        if ($segments[0] === '' || !array_key_exists($segments[0], $groups)) {
            return null;
        }

        return $segments[0];
    }

    /**
     * Получить группу роутов по первому фрагменту пути после префикса base
     *
     * @return class-string|null
     */
    public static function getGroup(string $path): ?string
    {
        $groupKey = self::getGroupKey(path: $path);

        if ($groupKey === null) {
            return null;
        }

        /** @var array<string, string> $groups */
        $groups = self::getConfigPart('groups') ?? [];

        foreach ($groups as $key => $group) {
            if ($key === $groupKey && class_exists($group)) {
                return $group;
            }
        }

        return null;
    }

    /**
     * Получить путь группы роутов, вызываемой по умолчанию
     *
     * На этот путь выполняется перенаправление с базового пути.
     */
    public static function getDefaultGroupPath(): string
    {
        /** @var string $default */
        $default = self::getConfigPart('default') ?? '';

        return $default === '' ? '/' : '/' . trim($default, '/') . '/';
    }
}
