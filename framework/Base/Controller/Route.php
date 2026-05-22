<?php

declare(strict_types=1);

namespace Simpledynamic\Base\Controller;

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
        private Closure|string $action,
        private string         $method,
        private string         $path,
        private string         $name,
        /** @var class-string|null */
        private ?string        $controllerName = null,
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
                    action: $callableAction,
                    method: (string) $value['method'],
                    path: (string) $value['path'],
                    name: (string) $key,
                );
            } elseif (is_array($value['action'])) {
                /** @var class-string $controllerName */
                $controllerName = $value['action'][0];

                $routes[$key] = new self(
                    action: (string) $value['action'][1],
                    method: (string) $value['method'],
                    path: (string) $value['path'],
                    name: (string) $key,
                    controllerName: $controllerName,
                );
            }
        }

        return $routes;
    }

    /**
     * Получить экшен
     */
    public function getAction(): Closure|string
    {
        return $this->action;
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
     * Получить имя контроллера
     *
     * @return class-string|null
     */
    public function getControllerName(): ?string
    {
        return $this->controllerName;
    }
}
