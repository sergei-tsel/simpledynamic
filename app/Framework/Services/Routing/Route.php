<?php

declare(strict_types=1);

namespace App\Framework\Services\Routing;

/**
 * Роут
 */
readonly class Route
{
    public function __construct(
        private string $method,
        private string $path,
        private string $controllerName,
        private string $actionName,
        private string $name,
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
                $key,
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
     * Получить имя контроллера
     */
    public function getControllerName(): string
    {
        return $this->controllerName;
    }

    /**
     * Получить имя экшена
     */
    public function getActionName(): string
    {
        return $this->actionName;
    }

    /**
     * Получить имя
     */
    public function getName(): string
    {
        return $this->name;
    }
}
