<?php

declare(strict_types=1);

namespace Simpledynamic\Container;

use Simpledynamic\Services\Reflection\ClassReflectionBuilder;
use Simpledynamic\Services\Reflection\MethodReflectionBuilder;

/**
 * Разрешитель зависимостей класса и его методов
 */
final class DependencyResolver
{
    public function __construct(
        private readonly ServiceContainer $container,
        private readonly ?ClassReflectionBuilder $classBuilder,
    ) {}

    /**
     * Построить экземпляр класса с разрешением зависимостей
     *
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function build(): object
    {
        if ($this->classBuilder === null) {
            throw new \InvalidArgumentException('ClassBuilder is required to build an instance');
        }

        $constructor = $this->classBuilder->getConstructor();

        if ($constructor === null) {
            return $this->classBuilder->newInstanceArgs();
        }

        $methodBuilder = new MethodReflectionBuilder(reflectionMethod: $constructor);
        $params = $methodBuilder->getParamsTypes();

        if ($params === []) {
            return $this->classBuilder->newInstanceArgs();
        }

        return $this->classBuilder->newInstanceArgs(array_values($this->resolveParameters($params)));
    }

    /**
     * Разрешить зависимости метода
     *
     * @param class-string $className
     * @return array<string, object> Разрешённые зависимости по именам параметров
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function resolveMethodDependencies(string $className, string $methodName): array
    {
        if (!method_exists($className, $methodName)) {
            return [];
        }

        $methodBuilder = new MethodReflectionBuilder(reflectionMethod: new \ReflectionMethod($className, $methodName));

        $params = $methodBuilder->getParamsTypes();

        if ($params === []) {
            return [];
        }

        return $this->resolveParameters($params);
    }

    /**
     * Разрешить параметры зависимостей
     *
     * Ключ результата совпадает с именем параметра метода: зависимости передаются
     * как именованные аргументы и не должны смешиваться с переданными значениями.
     *
     * @param array<string, string> $params Типы параметров по именам
     * @return array<string, object> Разрешённые зависимости по именам параметров
     * @throws \Exception
     * @throws \ReflectionException
     */
    private function resolveParameters(array $params): array
    {
        $dependencies = [];

        foreach ($params as $name => $param) {
            if (!class_exists($param)) {
                continue;
            }

            $dependencies[$name] = $this->container->resolveDependency($param);
        }

        return $dependencies;
    }
}
