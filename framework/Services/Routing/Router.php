<?php

declare(strict_types=1);

namespace Framework\Services\Routing;

use config\Routes;
use Framework\Base\Controller\Middleware;
use Framework\Base\Controller\Route;
use Framework\Container\ProviderManager;
use Framework\Services\ParamsFiltration\Filter;
use Framework\Services\ParamsFiltration\FilterParam;
use Framework\Services\Reflection\ClassReflectionManager;
use Framework\Services\Reflection\MethodReflectionManager;

/**
 * Роутер
 */
class Router
{
    private static ?ClassReflectionManager $reflectionManager = null;

    /**
     * Обработать запрос
     */
    public static function handle(): mixed
    {
        $route = Routes::getByPath();

        if (!$route instanceof Route) {
            return null;
        }

        if ($route->getAction() !== null) {
            return $route->getAction()->call($route);
        }

        self::$reflectionManager = new ClassReflectionManager();

        $actionAttributes = self::$reflectionManager->readAttributes(class: $route->getControllerName(), memberName: $route->getActionName(), attributesNames: [
            'class'  => [
                Middleware::class,
            ],
            'member' => [
                Middleware::class,
                FilterParam::class,
            ],
        ]);

        $handledParams = [];

        /** @var Middleware $attribute */
        foreach ($actionAttributes['class'][Middleware::class] as $attribute) {
            $handledParams = self::callMiddleware(name: $attribute->getName(), handledParams: $handledParams);
        }

        /** @var Middleware $attribute */
        foreach ($actionAttributes['member'][Middleware::class] as $attribute) {
            $handledParams = self::callMiddleware(name: $attribute->getName(), handledParams: $handledParams);
        }

        return self::callAction(route: $route, handledParams: $handledParams);
    }

    /**
     * Вызвать мидлвар
     */
    private static function callMiddleware(string $name, array $handledParams = []): array
    {
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

        $middleware = new $name();

        return array_merge($handledParams, $middleware->handle($params, ...$dependencies));
    }

    /**
     * Вызвать экшен
     */
    private static function callAction(Route $route, array $handledParams = []): mixed
    {
        $inputParams = self::$reflectionManager->readAttributes(
            class: $route->getControllerName(),
            memberName: $route->getActionName(),
            attributesNames: [
                'member' => [
                    FilterParam::class,
                ],
            ],
        );

        $params = Routes::getPathParams();

        if ($route->getMethod() === 'POST') {
            $params['request'] = array_merge($handledParams, self::filterInputParams(filterParams: $inputParams['member'][FilterParam::class]));
        }

        $container = new ProviderManager()->buildContainer();

        return new MethodReflectionManager()->invoke(methodName: $route->getActionName(), class: $container->resolve($route->getControllerName()), args: $params);
    }

    /**
     * Отфильтровать внешние параметры
     */
    private static function filterInputParams(array $filterParams): array
    {
        if ($filterParams === []) {
            return [];
        }

        $filter = new Filter();
        $params = [];

        foreach ($filterParams as $attributeValue => $argument) {
            $inputType = InputType::tryFrom($attributeValue);

            $params[$inputType->name][] = match ($inputType) {
                InputType::POST,
                InputType::GET,
                InputType::COOKIE,
                InputType::ENV,
                InputType::SERVER  => $filter->inputVars(type: $inputType, args: $argument, addEmpty: false),
                InputType::FILES   => $filter->vars(vars: $_FILES, args: $argument, addEmpty: false),
                InputType::SESSION => $filter->vars(vars: $_SESSION, args: $argument, addEmpty: false),
            };
        };

        return $params;
    }
}
