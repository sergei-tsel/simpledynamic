<?php

declare(strict_types=1);

namespace Simpledynamic\Container;

use Simpledynamic\Services\Configuration\Config;
use Simpledynamic\Services\Reflection\ClassReflectionBuilder;
use Simpledynamic\Services\Reflection\MethodReflectionBuilder;

/**
 * Сервис-контейнер с маршрутизацией и мидлварами
 */
final class ServiceContainer
{
    /** @var array<class-string, array{concrete: callable|class-string, shared: bool}> */
    private array $bindings = [];

    /** @var array<class-string, object> */
    private array $instances = [];

    /** @var array<int, class-string|null> */
    private array $stack = [];

    private static ?ServiceContainer $instance = null;

    /**
     * Получить экземпляр контейнера
     *
     * Конфигурация передаётся только при первом обращении. Последующие вызовы без неё
     * не перестраивают контейнер, чтобы не терять зарегистрированные биндинги.
     */
    public static function getInstance(?Config $config = null): ServiceContainer
    {
        if (static::$instance === null) {
            static::$instance = new ProviderBootsrapper()->buildContainer($config);
        }

        return static::$instance;
    }

    /**
     * Установить экземпляр контейнера
     */
    public static function setInstance(?ServiceContainer $instance): void
    {
        static::$instance = $instance;
    }

    /**
     * Получить зарегистрированные экземпляры
     *
     * @return array<class-string, object>
     */
    public function getInstances(): array
    {
        return $this->instances;
    }

    /**
     * Зарегистрировать новый биндинг
     *
     * @param class-string $abstract
     * @param callable|class-string|null $concrete
     * @return void
     */
    public function bind(string $abstract, callable|string|null $concrete = null, bool $shared = false): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete ?? $abstract,
            'shared' => $shared,
        ];
    }

    /**
     * Зарегистрировать новый общий биндинг
     *
     * @param class-string $abstract
     * @param callable|class-string|null $concrete
     * @return void
     */
    public function singleton(string $abstract, callable|string|null $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Проверить наличие зарегистрированного биндинга
     */
    public function bound(string $abstract): bool
    {
        return array_key_exists($abstract, $this->bindings) || array_key_exists($abstract, $this->instances);
    }

    /**
     * Получить объект по ключу биндинга
     *
     * @param class-string $className
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function make(string $className): object
    {
        return $this->resolveDependency($className);
    }

    /**
     * Разрешить объект
     *
     * @param class-string $className
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function resolve(string $className): object
    {
        if (in_array($className, $this->stack, true)) {
            throw new \Exception('Кольцевая зависимость от ' . $className);
        }

        $this->stack[] = $className;

        try {
            return new DependencyResolver(
                $this,
                new ClassReflectionBuilder(reflectionClass: new \ReflectionClass($className)),
            )->build();
        } finally {
            array_pop($this->stack);
        }
    }

    /**
     * Вызвать произвольный метод класса с параметрами и зависимостями из контейнера
     *
     * @param class-string $className
     * @param array<string, mixed> $params
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function execute(string $className, string $methodName, array $params = []): mixed
    {
        if (!method_exists($className, $methodName)) {
            return null;
        }

        return $this->executeOn(
            object: $this->resolve($className),
            className: $className,
            methodName: $methodName,
            params: $params,
        );
    }

    /**
     * Вызвать метод уже разрешённого экземпляра
     *
     * Вызывается, когда экземпляр уже создан и настроен вызывающим кодом: разрешение
     * заново создало бы другой объект и потеряло его состояние.
     *
     * @param array<string, mixed> $params
     * @param class-string $className Название класса, которому принадлежит метод
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function executeOn(object $object, string $className, string $methodName, array $params = []): mixed
    {
        if (!method_exists($object, $methodName)) {
            return null;
        }

        $methodBuilder = new MethodReflectionBuilder(reflectionMethod: new \ReflectionMethod($className, $methodName));

        // Переданные значения имеют приоритет над зависимостями из контейнера
        return $methodBuilder->invoke(object: $object, args: array_merge(
            new DependencyResolver($this, null)->resolveMethodDependencies($className, $methodName),
            $params,
        ));
    }

    /**
     * Разрешить зависимость
     *
     * @param class-string $className
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function resolveDependency(string $className): object
    {
        if (!array_key_exists($className, $this->bindings)) {
            return $this->resolve($className);
        }

        $binding = $this->bindings[$className];

        if ($binding['shared'] && array_key_exists($className, $this->instances)) {
            return $this->instances[$className];
        }

        /** @var object $instance */
        $instance = is_callable($binding['concrete'])
            ? $binding['concrete']($this)
            : $this->resolve($binding['concrete']);

        if ($binding['shared']) {
            $this->instances[$className] = $instance;
        }

        return $instance;
    }
}
