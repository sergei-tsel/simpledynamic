<?php

declare(strict_types=1);

namespace App\Framework\Services\Routing;

use App\Framework\Services\Reflection\MethodReflectionManager;
use App\Infrastructure\Container\ProviderManager;

/**
 * Роут
 */
readonly class Route
{
    public function __construct(
        private string       $method,
        private string       $path,
        private string       $controller,
        private string       $action,
        private string       $name,
        private string|array $middlewares = [],
    ) {
    }

    /**
     * Создать массив роутов из двухуровневого вложенного массива
     *
     * @return Route[]
     */
    public static function instanceMany(array $data): array
    {
        if ($data === []) {
            return [];
        }

        $routes = [];

        foreach ($data as $key => $value) {
            if (!is_array($value) || $value === []) {
                continue;
            }

            $routes[$key] = new self(
                $value['method'],
                $value['path'],
                $value['action'][0],
                $value['action'][1],
                $value['name'],
                $value['middlewares'] ?? [],
            );
        }

        return $routes;
    }

    /**
     * Получить HTTP-метод
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Получить путь
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Получить имя
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Вызвать экшен
     */
    public function callAction(): void
    {
        $params = new ParamsFilter()
            ->readAttributes($this->controller, $this->action)
            ->getFilteredData();

        if ($this->middlewares) {
            $handledParams     = $this->callMiddlewares();
            $params['handled'] = $handledParams;
        }

        $params = array_key_exists('path', $params)
            ? [$params, ...$params['path']]
            : $params;

        $container = new ProviderManager()->buildContainer();

        new MethodReflectionManager()
            ->invoke($this->action, $container->resolve($this->controller), $params);
    }

    /**
     * Вызвать мидлвары
     */
    private function callMiddlewares(): array
    {
        if (is_string($this->middlewares)) {
            return $this->callMiddleware($this->middlewares);
        }

        $handledParams = [];

        foreach ($this->middlewares as $name) {
            $handledParams = array_merge($handledParams, $this->callMiddleware($name, $handledParams));
        }

        return $handledParams;
    }

    /**
     * Вызвать мидлвар
     */
    private function callMiddleware(string $name, array $handledParams = []): array
    {
        $params = new ParamsFilter()
            ->readAttributes($name)
            ->getFilteredData();
        $params['handled'] = $handledParams;

        $middleware = new $name();
        return $middleware->handle($params);
    }
}
