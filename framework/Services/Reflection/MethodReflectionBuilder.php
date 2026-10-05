<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Reflection;

use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Simpledynamic\Base\Model\BuilderInterface;
use Simpledynamic\Services\Logging\Logger;

/**
 * Билдер для работы с методом через рефлексию
 */
final readonly class MethodReflectionBuilder implements BuilderInterface
{
    public function __construct(
        private ReflectionMethod $reflectionMethod,
    ) {}

    /**
     * Получить объект рефлексии класса
     */
    public function getReflectionClass(): ReflectionClass
    {
        return $this->reflectionMethod->getDeclaringClass();
    }

    /**
     * Получить объект рефлексии метода
     */
    public function getReflectionMethod(): ?ReflectionMethod
    {
        return $this->reflectionMethod;
    }

    /**
     * Вызвать метод с аргументами
     *
     * @param array<array-key, mixed> $args
     */
    public function invoke(?object $object, array $args = []): mixed
    {
        try {
            return $this->reflectionMethod->invokeArgs($object, $args);
        } catch (\ReflectionException $exception) {
            // Молчаливый возврат null здесь скрывал бы ошибки вызова: через invoke
            // работают фасады и ServiceContainer::executeOn, поэтому неудачный вызов
            // обязан попасть в лог
            Logger::report($exception);

            return null;
        }
    }

    /**
     * Получить имена параметров метода
     *
     * @return array<array-key, string>
     */
    public function getParamsNames(): array
    {
        return array_map(
            static fn(ReflectionParameter $param): string => $param->getName(),
            $this->reflectionMethod->getParameters(),
        );
    }

    /**
     * Получить типы данных параметров метода
     *
     * @return array<string, string>
     */
    public function getParamsTypes(): array
    {
        $paramsTypes = [];

        foreach ($this->reflectionMethod->getParameters() as $reflectionParam) {
            $paramType = $reflectionParam->getType();

            if ($paramType !== null) {
                $paramsTypes[$reflectionParam->getName()] = match (true) {
                    $paramType instanceof \ReflectionNamedType => $paramType->getName(),
                    $paramType instanceof \ReflectionUnionType,
                    $paramType instanceof \ReflectionIntersectionType,
                        => (string) $paramType,
                };
            }
        }

        return $paramsTypes;
    }

    /**
     * Получить параметры метода (без создания лишних объектов рефлексии)
     *
     * @return array<array-key, ReflectionParameter>
     */
    public function getParameters(): array
    {
        return $this->reflectionMethod->getParameters();
    }

    /**
     * Получить параметр по имени
     */
    public function getParameter(string $name): ?ReflectionParameter
    {
        foreach ($this->reflectionMethod->getParameters() as $parameter) {
            if ($parameter->getName() === $name) {
                return $parameter;
            }
        }

        return null;
    }
}
