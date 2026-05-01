<?php

declare(strict_types=1);

namespace Sympledynamic\Services\Routing;

use config\Routes;
use Sympledynamic\Base\Controller\Middleware;
use Sympledynamic\Base\Controller\Route;
use Sympledynamic\Container\ProviderManager;
use Sympledynamic\Services\Filtration\Filter;
use Sympledynamic\Services\Filtration\FilterParam;
use Sympledynamic\Services\Reflection\ClassReflectionManager;
use Sympledynamic\Services\Reflection\MethodReflectionManager;

/**
 * Роутер
 */
final class Router
{
    private static ClassReflectionManager $reflectionManager;

    /**
     * Обработать запрос
     *
     * @psalm-suppress PossiblyUnusedReturnValue
     */
    public static function handle(): mixed
    {
        $route = Routes::getByPath();

        if (!$route instanceof Route) {
            return null;
        }

        $action = $route->getAction();

        if ($action instanceof \Closure) {
            return $action->call($route);
        }

        $controllerName = $route->getControllerName();

        if ($controllerName === null || !class_exists($controllerName) || !method_exists($controllerName, $action)) {
            return null;
        }

        /**
         * @var class-string
         * @var string $action
         */
        return self::call(controllerName: $controllerName, methodName: $action);
    }

    /**
     * Вызвать экшен
     *
     * @param class-string $controllerName
     */
    public static function call(string $controllerName, string $methodName): mixed
    {
        self::$reflectionManager = new ClassReflectionManager();

        /** @var array<string, array<class-string<Middleware>, list<Middleware>>> $actionAttributes */
        $actionAttributes = self::$reflectionManager->readAttributes(class: $controllerName, memberName: $methodName, attributesNames: [
            'class'  => [
                Middleware::class,
            ],
            'member' => [
                Middleware::class,
            ],
        ]);

        $handledParams = [];

        foreach ($actionAttributes['class'][Middleware::class] as $attribute) {
            $handledParams = self::callMiddleware(name: $attribute->getName(), handledParams: $handledParams);
        }

        foreach ($actionAttributes['member'][Middleware::class] as $attribute) {
            $handledParams = self::callMiddleware(name: $attribute->getName(), handledParams: $handledParams);
        }

        return self::callControllerMethod(controllerName: $controllerName, methodName: $methodName, handledParams: $handledParams);
    }


    /**
     * Вызвать мидлвар
     *
     * @param class-string $name
     */
    private static function callMiddleware(string $name, array $handledParams = []): array
    {
        /** @var array<string, array<class-string<FilterParam>, list<FilterParam>>> $params */
        $params = self::$reflectionManager->readAttributes(
            class: $name,
            memberName: 'handle',
            attributesNames: [
                'member' => [
                    FilterParam::class,
                ],
            ],
        );

        $params = self::filterInputParams(filterParams: $params['member'][FilterParam::class]);
        $params['handled'] = $handledParams;

        $container = new ProviderManager()->buildContainer();
        $dependencies = $container->resolveMethodDependencies(className: $name, methodName: 'handle');

        /** @psalm-suppress MixedMethodCall */
        $middleware = new $name();

        /** @psalm-suppress MixedMethodCall */
        $middlewareHandledParams = $middleware->handle($params, ...$dependencies);

        if (!is_array($middlewareHandledParams)) {
            return [];
        }

        return array_merge($handledParams, $middlewareHandledParams);
    }

    /**
     * Вызвать метод контроллера
     *
     * @param class-string $controllerName
     */
    private static function callControllerMethod(string $controllerName, string $methodName, array $handledParams = []): mixed
    {
        /** @var array<string, array<class-string<FilterParam>, list<FilterParam>>> $inputParams */
        $inputParams = self::$reflectionManager->readAttributes(
            class: $controllerName,
            memberName: $methodName,
            attributesNames: [
                'member' => [
                    FilterParam::class,
                ],
            ],
        );

        $params = Routes::getPathParams() ?? [];

        $params['request'] = array_merge($handledParams, self::filterInputParams(filterParams: $inputParams['member'][FilterParam::class]));

        $container = new ProviderManager()->buildContainer();

        return new MethodReflectionManager()->invoke(methodName: $methodName, class: $container->resolve($controllerName), args: $params);
    }

    /**
     * Отфильтровать внешние параметры
     *
     * @param array<int, FilterParam> $filterParams
     */
    private static function filterInputParams(array $filterParams): array
    {
        if ($filterParams === []) {
            return [];
        }

        $filter = new Filter();
        $params = [];

        foreach ($filterParams as $filterParam) {
            $inputType = $filterParam->getInputType();

            if ($inputType === null) {
                $varName = $filterParam->getVarName();

                if ($varName === null) {
                    continue;
                }

                $params[$filterParam->getType()->name][] = match ($filterParam->getType()) {
                    GlobalArray::FILES   => isset($_FILES[$varName]) ? $filter->varValue(value: $_FILES[$varName], arg: $filterParam) : null,
                    GlobalArray::SESSION => isset($_SESSION[$varName]) && (is_array($_SESSION[$varName]) || is_scalar($_SESSION[$varName]))
                        ? $filter->varValue(value: $_SESSION[$varName], arg: $filterParam)
                        : null,
                };
            } else {
                $params[$inputType->name][] = $filter->inputVarValue(arg: $filterParam);
            }
        }

        return $params;
    }
}
