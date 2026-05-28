<?php

declare(strict_types=1);

namespace Simpledynamic\Providers;

use Simpledynamic\Container\ServiceContainer;

/**
 * Фасад
 *
 * @api
 */
abstract class Facade
{
    /**
     * Вызвать метод с аргументами
     *
     * @psalm-suppress PossiblyUnusedMethod
     * @param class-string $method
     */
    public static function __callStatic(string $method, array $args): mixed
    {
        $instance = ServiceContainer::getInstance()->make(static::getFacadeAccessor());

        /** @psalm-suppress MixedMethodCall */
        return $instance->$method(...$args);
    }

    /**
     * Получить акксесор фасада
     *
     * @psalm-suppress PossiblyUnusedMethod
     * @return class-string
     */
    protected static function getFacadeAccessor(): string
    {
        return Facade::class;
    }
}
