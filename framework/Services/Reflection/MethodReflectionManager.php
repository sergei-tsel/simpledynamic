<?php

declare(strict_types=1);

namespace Framework\Services\Reflection;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Сервис для управления методами с помощью рефлексии
 */
class MethodReflectionManager
{
    use InstanceableReflectionAttributes;

    /**
     * Вызвать метод с аргументами
     */
    public function invoke(string $methodName, object|string|null $class = null, array $args = []): mixed
    {
        $reflectionMethod = $this->create($methodName, $class);

        try {
            return $reflectionMethod?->invokeArgs(new $class(), $args);
        } catch (ReflectionException) {
            return null;
        }
    }

    /**
     * Получить типы данных параметров метода
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
            $paramsTypes[$reflectionParam->getName()] = $reflectionParam->getType()?->getName();
        }

        return $paramsTypes;
    }

    /**
     * Создать объект рефлексии метода для анализа класса
     */
    private function create(string $methodName, object|string|null $class = null): ?ReflectionMethod
    {
        try {
            if ($class === null) {
                return ReflectionMethod::createFromMethodName($methodName);
            }

            if (!new ReflectionClass($class)->hasMethod($methodName)) {
                return null;
            }

            return new ReflectionMethod($class, $methodName);
        } catch (ReflectionException) {
            return null;
        }
    }
}
