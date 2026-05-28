<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Reflection;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Сервис для управления методами с помощью рефлексии
 */
final class MethodReflectionManager
{
    use InstanceableReflectionAttributes;

    /**
     * Вызвать метод с аргументами
     *
     * @param object|class-string|null $class
     */
    public function invoke(string $methodName, object|string|null $class = null, array $args = []): mixed
    {
        $reflectionMethod = $this->create($methodName, $class);

        try {
            if ($class === null || is_object($class)) {
                return $reflectionMethod?->invokeArgs($class, $args);
            }

            return null;
        } catch (ReflectionException) {
            return null;
        }
    }

    /**
     * Получить имена параметров метода
     *
     * @param object|class-string|null $class
     * @return array<int, string>
     */
    public function getParamsNames(string $methodName, object|string|null $class = null): array
    {
        $reflectionMethod = $this->create($methodName, $class);

        if ($reflectionMethod === null) {
            return [];
        }

        $reflectionParams = $reflectionMethod->getParameters();

        $paramsNames = [];

        foreach ($reflectionParams as $reflectionParam) {
            $paramsNames[] = $reflectionParam->getName();
        }

        return $paramsNames;
    }

    /**
     * Получить типы данных параметров метода
     *
     * @param object|class-string|null $class
     * @return array<string, string>
     */
    public function getParamsTypes(string $methodName, object|string|null $class = null): array
    {
        $reflectionMethod = $this->create($methodName, $class);

        if ($reflectionMethod === null) {
            return [];
        }

        $reflectionParams = $reflectionMethod->getParameters();

        $paramsTypes = [];

        foreach ($reflectionParams as $reflectionParam) {
            $paramType = $reflectionParam->getType();

            if ($paramType !== null) {
                $paramsTypes[$reflectionParam->getName()] = match (true) {
                    $paramType instanceof \ReflectionNamedType        => $paramType->getName(),
                    $paramType instanceof \ReflectionUnionType,
                    $paramType instanceof \ReflectionIntersectionType => (string) $paramType,
                };
            }
        }

        return $paramsTypes;
    }

    /**
     * Создать объект рефлексии метода для анализа класса
     *
     * @param object|class-string|null $class
     */
    private function create(string $methodName, object|string|null $class = null): ?ReflectionMethod
    {
        try {
            if ($class === null) {
                if (!str_contains($methodName, '::')) {
                    return null;
                }

                return ReflectionMethod::createFromMethodName($methodName);
            }

            $reflectionClass = new ReflectionClass($class);

            if (!$reflectionClass->hasMethod($methodName)) {
                return null;
            }

            return $reflectionClass->getMethod($methodName);
        } catch (ReflectionException) {
            return null;
        }
    }
}
