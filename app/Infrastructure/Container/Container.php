<?php

declare(strict_types=1);

namespace App\Infrastructure\Container;

use App\Framework\Services\Reflection\MethodReflectionManager;

/**
 * Сервис-контейнер
 */
final class Container
{
    protected array $bindings = [];

    protected array $instances = [];

    protected array $stack = [];

    /**
     * Зарегистрировать новый биндинг
     */
    public function bind(string $abstract, callable|string $concrete = '', bool $shared = false): void {
        $this->bindings[$abstract] = [
            'concrete' => $concrete ?: $abstract,
            'shared'   => $shared,
        ];
    }

    /**
     * Зарегистрировать новый общий биндинг
     */
    public function singleton(string $abstract, callable|string $concrete = ''): void {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Проверить наличие зарегистрированного биндинга
     */
    public function bound(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }

    /**
     * Построить объект
     */
    public function build(string $className): object
    {
        $params = new MethodReflectionManager()->getParamsTypes($className);

        if ($params === []) {
            return new $className();
        }

        $dependencies = [];

        foreach ($params as $param) {
            if ($param !== null && class_exists($param)) {
                $dependencies[] = $this->resolveDependency($param);
            }
        }

        return new $className(...$dependencies);
    }

    /**
     * Разрешить объект
     *
     * @throws \Exception
     */
    public function resolve(string $className): object
    {
        if (in_array($className, $this->stack)) {
            throw new \Exception('Кольцевая зависимость');
        }

        $this->stack[] = $className;

        return $this->build($className);
    }

    /**
     * Разрешить зависимость
     */
    protected function resolveDependency(string $name): ?object
    {
        if (!isset($this->bindings[$name])) {
            $this->resolve($name);
        }

        $binding = $this->bindings[$name];

        if ($binding['shared'] && isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        $concrete = $binding['concrete'] instanceof \Closure
            ? $binding['concrete']()
            : $binding['concrete'];

        $instance = $this->resolve($concrete);

        if ($binding['shared']) {
            $this->instances[$name] = $instance;
        }

        return $instance;
    }
}
