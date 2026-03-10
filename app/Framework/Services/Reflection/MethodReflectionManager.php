<?php

declare(strict_types=1);

namespace App\Framework\Services\Reflection;

use ReflectionClass;
use ReflectionMethod;

/**
 * Сервис для управления методами с помощью рефлексии
 */
class MethodReflectionManager
{
    /**
     * Вызвать метод и передать ему массив аргументов
     *
     * @throws \ReflectionException
     */
    public function invoke(string $methodName, object|string|null $class = null, array $args = []): mixed
    {
        if ($class === null) {
            $reflectionMethod = ReflectionMethod::createFromMethodName($methodName);
        } else {
            $reflectionMethod = new ReflectionMethod($class, $methodName);
        }

        return $reflectionMethod->invokeArgs(new $class(), $args);
    }

    /**
     * Разрешить зависимости метода
     *
     * @throws \ReflectionException
     */
    public function getParamsTypes(string $class, ?string $methodName = null): array
    {
        $reflectionClass = new ReflectionClass($class);
        $paramsTypes = [];

        if ($methodName === null) {
            $method = $reflectionClass->getConstructor();

            if ($method === null) {
                return [];
            }
        } elseif ($reflectionClass->hasMethod($methodName)) {
            $method = $reflectionClass->getMethod($methodName);
        } else {
            return $paramsTypes;
        }

        $params = $method->getParameters();

        foreach ($params as $param) {
            $paramsTypes[] = $param->getType()?->getName();
        }

        return $paramsTypes;
    }
}
