<?php

declare(strict_types=1);

namespace Sympledynamic\Base\Controller;

use Closure;

/**
 * Роут
 *
 * @api
 * @psalm-suppress ClassCanBeFinal
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
                /** @var Closure $callableAction */
                $callableAction = is_a($value['action'], Closure::class) ? $value['action'] : ($value['action'])(...);

                $routes[$key] = new self(
                    method: (string) $value['method'],
                    path: (string) $value['path'],
                    name: (string) $key,
                    action: $callableAction,
                );
            } elseif (is_array($value['action'])) {
                $routes[$key] = new self(
                    method: (string) $value['method'],
                    path: (string) $value['path'],
                    name: (string) $key,
                    controllerName: (string) $value['action'][0],
                    actionName: (string) $value['action'][1],
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
     *
     * @psalm-suppress PossiblyUnusedMethod
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
