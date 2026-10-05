<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Routing;

use Closure;
use Simpledynamic\Base\Controller\RouteInterface;
use Simpledynamic\Base\Model\BuilderInterface;

/**
 * Билдер для роута
 */
final class RouteBuilder implements BuilderInterface
{
    /**
     * Собрать роут из данных группы роутов
     *
     * Роут связывает HTTP-метод, путь и экшен: вызывается запросом с этим методом,
     * выбирается по совпадению пути и вызывает указанный экшен.
     *
     * @param array{method: string, action: mixed} $route Данные роута
     * @param string $name Имя роута
     * @param array<string, string> $pathParams Значения PATH-параметров роута
     */
    public function buildRoute(array $route, string $name, array $pathParams = []): ?RouteInterface
    {
        $action = $this->buildAction($route['action'] ?? null);

        if ($action === null || !$this->hasMethod($route)) {
            return null;
        }

        if ($action instanceof Closure) {
            return new ClosureRoute(action: $action, method: $route['method'], path: $name, pathParams: $pathParams);
        }

        return new ControllerRoute(
            controllerName: $action['class'],
            action: $action['method'],
            method: $route['method'],
            path: $name,
            pathParams: $pathParams,
        );
    }

    /**
     * Проверить наличие названия HTTP-метода в данных роута
     *
     * @param array<array-key, mixed> $route Данные роута
     */
    private function hasMethod(array $route): bool
    {
        if (!array_key_exists('method', $route)) {
            return false;
        }

        return is_string($route['method']) && $route['method'] !== '';
    }

    /**
     * Собрать экшен
     *
     * @param mixed $action Экшен
     * @return Closure|array{class: class-string, method: string}|null
     */
    private function buildAction(mixed $action): Closure|array|null
    {
        if ($action instanceof Closure) {
            return $action;
        }

        if (!is_array($action)) {
            return null;
        }

        if (
            !array_key_exists('class', $action)
            || !array_key_exists('method', $action)
            || !is_string($action['class'])
            || !is_string($action['method'])
            || !class_exists($action['class'])
            || !method_exists($action['class'], $action['method'])
        ) {
            return null;
        }

        return [
            'class' => $action['class'],
            'method' => $action['method'],
        ];
    }
}
