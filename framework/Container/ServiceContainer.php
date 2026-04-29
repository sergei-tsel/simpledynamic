<?php

declare(strict_types=1);

namespace Sympledynamic\Container;

use Sympledynamic\Services\Reflection\MethodReflectionManager;

/**
 * Сервис-контейнер
 *
 * @api
 */
final class ServiceContainer
{
    /** @var array<class-string, array{concrete: callable|class-string, shared: bool}>|null[]*/
    protected array $bindings = [];

    /** @var array<class-string, object>|null[] */
    protected array $instances = [];

    /** @var array<int, class-string>|null[] */
    protected array $stack = [];

    /**
     * Зарегистрировать новый биндинг
     *
     * @param class-string $abstract
     * @param callable|class-string|null $concrete
     * @param bool $shared
     * @return void
     */
    public function bind(string $abstract, callable|string|null $concrete = null, bool $shared = false): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete ?? $abstract,
            'shared'   => $shared,
        ];
    }

    /**
     * Зарегистрировать новый общий биндинг
     *
     * @param class-string $abstract
     * @param callable|class-string $concrete
     * @return void
     */
    public function singleton(string $abstract, callable|string $concrete = ''): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Проверить наличие зарегистрированного биндинга
     *
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function bound(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }

    /**
     * Построить объект
     *
     * @param class-string $className
     * @return object
     */
    public function build(string $className): object
    {
        /** @var array<string, class-string> $params */
        $params = new MethodReflectionManager()->getParamsTypes(methodName: '__construct', class: $className);

        if ($params === []) {
            /** @psalm-suppress MixedMethodCall */
            return new $className();
        }

        $dependencies = [];

        foreach ($params as $param) {
            if (class_exists($param)) {
                $dependencies[] = $this->resolveDependency($param);
            }
        }

        /** @psalm-suppress MixedMethodCall */
        return new $className(...$dependencies);
    }

    /**
     * Разрешить объект
     *
     * @param class-string $className
     * @return object
     */
    public function resolve(string $className): object
    {
        if (in_array($className, $this->stack)) {
            try {
                throw new \Exception('Кольцевая зависимость от ' . $className);
            } catch (\Throwable) {
            }
        }

        $this->stack[] = $className;

        return $this->build($className);
    }

    /**
     * Разрешить зависимости метода
     *
     * @param class-string $className
     * @return array<object|null>
     */
    public function resolveMethodDependencies(string $className, string $methodName): array
    {
        $methodManager = new MethodReflectionManager();

        /** @var array<string, class-string> $params */
        $params = $methodManager->getParamsTypes(methodName: $methodName, class: $className);

        if ($params === []) {
            return [];
        }

        $dependencies = [];

        foreach ($params as $param) {
            if (class_exists($param)) {
                $dependencies[] = $this->resolveDependency($param);
            }
        }

        return $dependencies;
    }

    /**
     * Разрешить зависимость
     *
     * @param class-string $name
     * @return object|null
     */
    protected function resolveDependency(string $name): ?object
    {
        if (!isset($this->bindings[$name])) {
            return $this->resolve($name);
        }

        $binding = $this->bindings[$name];

        if ($binding['shared'] && isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        /** @var object|null $instance */
        $instance = is_callable($binding['concrete'])
            ? $binding['concrete']($this)
            : $this->resolve($binding['concrete']);

        if ($binding['shared']) {
            $this->instances[$name] = $instance;
        }

        /** @psalm-suppress MixedReturnStatement */
        return $instance;
    }
}
