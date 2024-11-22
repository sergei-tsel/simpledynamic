<?php

declare(strict_types=1);

namespace App\Controller\Routes;

use ReflectionMethod;

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

        $reflectionMethod = new ReflectionMethod($this->controller, $this->action);
        $reflectionMethod->invokeArgs(new $this->controller(), [$params, ...$params['path']]);
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
