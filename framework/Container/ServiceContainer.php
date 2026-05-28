<?php

declare(strict_types=1);

namespace Simpledynamic\Container;

use Simpledynamic\Services\Reflection\MethodReflectionManager;

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

    protected static ?ServiceContainer $instance = null;

    /**
     * Получить экземпляр контейнера
     */
    public static function getInstance(): ServiceContainer
    {
        if (static::$instance === null) {
            static::$instance = new ProviderManager()->buildContainer();
        }

        return static::$instance;
    }

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
     * Получить объект по ключу биндинга
     *
     * @param class-string $className
     * @return object
     */
    public function make(string $className): object
    {
        return $this->resolveDependency($className);
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

        try {
            return $this->build($className);
        } finally {
            array_pop($this->stack);
        }
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
     * @param class-string $className
     * @return object
     */
    protected function resolveDependency(string $className): object
    {
        if (!isset($this->bindings[$className])) {
            return $this->resolve($className);
        }

        $binding = $this->bindings[$className];

        if ($binding['shared'] && isset($this->instances[$className])) {
            return $this->instances[$className];
        }

        /** @var object $instance */
        $instance = is_callable($binding['concrete'])
            ? $binding['concrete']($this)
            : $this->resolve($binding['concrete']);

        if ($binding['shared']) {
            $this->instances[$className] = $instance;
        }

        /** @psalm-suppress MixedReturnStatement */
        return $instance;
    }
}
