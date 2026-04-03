<?php

declare(strict_types=1);

namespace App\Framework\Services\Routing;

use App\Framework\Services\ParamsFiltration\Filter;
use App\Framework\Services\ParamsFiltration\FilterParam;
use App\Framework\Services\Reflection\MethodReflectionManager;
use App\Framework\Services\Reflection\ClassReflectionManager;
use App\Infrastructure\Container\ProviderManager;
use config\Routes;

/**
 * Роутер
 */
class Router
{
    private static ?ClassReflectionManager $reflectionManager = null;

    /**
     * Обработать запрос
     *
     * @throws \Exception
     */
    public static function handle(): void
    {
        $route = Routes::getByPath();

        if ($route instanceof Route) {
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
                $handledParams = self::callMiddleware(
                    name: $attribute->getName(),
                    handledParams: $handledParams,
                );
            }

            /** @var Middleware $attribute */
            foreach ($actionAttributes['member'][Middleware::class] as $attribute) {
                $handledParams = self::callMiddleware(
                    name: $attribute->getName(),
                    handledParams: $handledParams,
                );
            }

            self::callAction(route: $route, handledParams: $handledParams);
        }
    }

    /**
     * Вызвать мидлвар
     *
     * @throws \Exception
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
     *
     * @throws \Exception
     */
    private static function callAction(Route $route, array $handledParams = []): void
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

        new MethodReflectionManager()->invoke(methodName: $route->getActionName(), class: $container->resolve($route->getControllerName()), args: $params);
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
            $inputType = InputTypes::tryFrom($attributeValue);

            $params[$inputType->name][] = match ($inputType) {
                InputTypes::POST,
                InputTypes::GET,
                InputTypes::COOKIE,
                InputTypes::ENV,
                InputTypes::SERVER  => $filter->inputVars(type: $inputType, args: $argument, addEmpty: false),
                InputTypes::FILES   => $filter->vars(vars: $_FILES, args: $argument, addEmpty: false),
                InputTypes::SESSION => $filter->vars(vars: $_SESSION, args: $argument, addEmpty: false),
            };
        };

        return $params;
    }
}
