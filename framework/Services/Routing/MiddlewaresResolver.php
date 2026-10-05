<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Routing;

use ReflectionClass;
use Simpledynamic\Container\ServiceContainer;
use Simpledynamic\Services\Configuration\Config;
use Simpledynamic\Services\Reflection\ClassReflectionBuilder;

/**
 * Разрешатель мидлваров
 */
final class MiddlewaresResolver
{
    public function __construct(
        private readonly InputResolver $inputResolver,
    ) {}

    /**
     * Разрешить мидлвары класса или класса и его метода
     *
     * @param class-string $className Название класса
     * @param string|null $methodName Название метода
     * @param array<string, mixed> $params Список внешних параметров
     * @return array<string, mixed> Обработанный список внешних параметров
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function processAttributeFor(
        string $className,
        ?string $methodName = null,
        array $params = [],
        ?Config $config = null,
    ): array {
        foreach ($this->readMiddlewares(className: $className, methodName: $methodName) as $middleware) {
            $params = $this->call(middleware: $middleware, params: $params, config: $config);
        }

        return $params;
    }

    /**
     * Вызвать мидлвар со списком параметров
     *
     * @param array<string, mixed> $params Список внешних параметров
     * @return array<string, mixed> Обработанный список внешних параметров
     * @throws \Exception
     * @throws \ReflectionException
     */
    private function call(Middleware $middleware, array $params = [], ?Config $config = null): array
    {
        $middlewareName = $middleware->getName();

        if (!class_exists($middlewareName) || !method_exists($middlewareName, 'handle')) {
            return $params;
        }

        $inputParams = $this->inputResolver->applyAttributeTo(className: $middlewareName, methodName: 'handle');

        if ($inputParams !== []) {
            $params = array_merge($params, $inputParams);
        }

        /** @var mixed $handleResult */
        $handleResult = ServiceContainer::getInstance($config)->execute(
            className: $middlewareName,
            methodName: 'handle',
            params: [
                'request' => $params,
            ],
        );

        if (!is_array($handleResult)) {
            return $params;
        }

        /** @var array<string, mixed> $handleResult */
        return array_merge($params, $handleResult);
    }

    /**
     * Прочитать мидлвары класса и его метода
     *
     * @param class-string $className Название класса
     * @return list<Middleware>
     * @throws \ReflectionException
     */
    private function readMiddlewares(string $className, ?string $methodName = null): array
    {
        if (!class_exists($className) || $methodName !== null && !method_exists($className, $methodName)) {
            return [];
        }

        $attributes = new ClassReflectionBuilder(
            reflectionClass: new ReflectionClass($className),
        )->readAttributes(memberName: $methodName, attributesNames: [
            'class' => [
                Middleware::class,
            ],
            'member' => [
                Middleware::class,
            ],
        ]);

        return array_values(array_filter(
            [...($attributes['class'][Middleware::class] ?? []), ...($attributes['member'][Middleware::class] ?? [])],
            static fn(object $middleware): bool => $middleware instanceof Middleware,
        ));
    }
}
