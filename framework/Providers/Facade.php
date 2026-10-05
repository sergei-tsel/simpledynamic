<?php

declare(strict_types=1);

namespace Simpledynamic\Providers;

use Simpledynamic\Container\ServiceContainer;
use Simpledynamic\Services\Reflection\ClassReflectionBuilder;
use Simpledynamic\Services\Reflection\MethodReflectionBuilder;

/**
 * Фасад
 */
abstract class Facade
{
    /**
     * Вызвать метод с аргументами
     *
     * @param string $method Название вызываемого метода
     * @param array<array-key, mixed> $args Аргументы вызываемого метода
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public static function __callStatic(string $method, array $args): mixed
    {
        $instance = static::resolveInstance();

        $classBuilder = new ClassReflectionBuilder(reflectionClass: new \ReflectionClass($instance));
        $reflectionMethod = $classBuilder->getReflectionMethod($method);

        if ($reflectionMethod === null) {
            throw new \RuntimeException(message: sprintf('Метод %s::%s() не найден', get_class($instance), $method));
        }

        $methodBuilder = new MethodReflectionBuilder(reflectionMethod: $reflectionMethod);
        return $methodBuilder->invoke(object: $instance, args: $args);
    }

    /**
     * Получить акксесор фасада
     *
     * @return class-string
     */
    protected static function getFacadeAccessor(): string
    {
        return Facade::class;
    }

    /**
     * Разрешить объект, к которому обращается фасад
     *
     * Фасад обращается к конфигурации через контейнер, а контейнер строится из той же
     * конфигурации. Чтобы разорвать это кольцо, объект ищется сначала среди готовых
     * экземпляров контейнера, и только затем разрешается через биндинги.
     *
     * @throws \Exception
     * @throws \ReflectionException
     */
    private static function resolveInstance(): object
    {
        $accessor = static::getFacadeAccessor();
        $container = ServiceContainer::getInstance();

        if ($container->bound($accessor)) {
            return $container->make($accessor);
        }

        foreach ($container->getInstances() as $instance) {
            if ($instance instanceof $accessor) {
                return $instance;
            }
        }

        throw new \RuntimeException(message: sprintf(
            'Фасад %s не связан с конфигурацией: в контейнере нет биндинга для %s. '
            . 'Передайте конфигурацию при первом вызове ServiceContainer::getInstance($config).',
            static::class,
            $accessor,
        ));
    }
}
