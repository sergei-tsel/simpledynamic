<?php

declare(strict_types=1);

namespace Framework\Base\Controller;

use Closure;

/**
 * Роут
 */
readonly class Route
{
    public function __construct(
        private string   $method,
        private string   $path,
        private string   $name,
        private ?Closure $action         = null,
        private ?string  $controllerName = null,
        private ?string  $actionName     = null,
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

            if (is_callable($value['action'])) {
                $routes[$key] = new self(
                    method: $value['method'],
                    path: $value['path'],
                    name: $key,
                    action: $value['action'],
                );
            } else {
                $routes[$key] = new self(
                    method: $value['method'],
                    path: $value['path'],
                    name: $key,
                    controllerName: $value['action'][0],
                    actionName: $value['action'][1],
                );
            }
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
     * Получить экшен
     */
    public function getAction(): ?Closure
    {
        return $this->action;
    }

    /**
     * Получить имя контроллера
     */
    public function getControllerName(): ?string
    {
        return $this->controllerName;
    }

    /**
     * Получить имя экшена
     */
    public function getActionName(): ?string
    {
        return $this->actionName;
    }
}
