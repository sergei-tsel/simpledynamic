<?php

declare(strict_types=1);

namespace Simpledynamic\Base\Controller;

use Closure;
use Simpledynamic\Services\Configuration\Routes;
use Simpledynamic\Services\Filtration\Sanitization\SanitizationFilter;
use Simpledynamic\Services\Filtration\Validation\ValidationFilter;
use Simpledynamic\Services\Filtration\Filter;
use Simpledynamic\Services\Filtration\FilterArgument;
use Simpledynamic\Services\Filtration\FilterParam;
use Simpledynamic\Services\Routing\InputType;
use Uri\Rfc3986\Uri;

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

    /**
     * Получить Uri
     */
    public static function getUri(): Uri
    {
        /**
         * @psalm-suppress UndefinedMagicMethod
         * @var string $base
         */
        $base = Routes::getConfigPart('base');

        $filter = new Filter();

        $sanitizedUrl = $filter->inputVarValue(
            arg: new FilterParam(type: InputType::SERVER, varName: 'REQUEST_URI', filter: SanitizationFilter::URL),
        );

        $validatedUrl = is_string($sanitizedUrl)
             ? $filter->varValue(
                 value: $base . $sanitizedUrl,
                 arg: new FilterArgument(filter: ValidationFilter::URL),
             )
             : null;

        return new Uri(uri: is_string($validatedUrl) ? $validatedUrl : '/', baseUrl: new Uri($base));
    }

    /**
     * Получить параметры пути
     */
    public static function getPathParams(): array|null
    {
        $route = self::getByPath();

        if ($route === null) {
            return null;
        }

        $routePath = preg_filter(['#\{/u', '/}#u'], ['(?P<', '>\d+)'], $route->getPath());

        if (!is_string($routePath)) {
            return null;
        }

        $routePath = '^' . $routePath . '$';

        $params = [];

        $path = self::getUri()->getPath();
        mb_ereg($routePath, $path, $params);

        return $params;
    }

    /**
     * Получить роут по пути
     */
    public static function getByPath(): Route|null
    {
        /**
         * @psalm-suppress UndefinedMagicMethod
         * @var array<string, Route> $routes
         */
        $routes = Routes::get();

        if ($routes === []) {
            return null;
        }

        $filteredRoutes = array_filter($routes, function (Route $route): bool {
            $routePath = preg_replace('#\{/d+}#u', '\d+', $route->getPath());

            if (!is_string($routePath)) {
                return false;
            }

            $routePath = '^' . $routePath . '$';

            $path = self::getUri()->getPath();

            $requestMethod = new Filter()->inputVarValue(
                arg: new FilterParam(type: InputType::SERVER, varName: 'REQUEST_METHOD', filter: SanitizationFilter::FULL_SPECIAL_CHARS),
            );

            return mb_ereg_match($routePath, $path) && $route->getMethod() === (is_string($requestMethod) ? $requestMethod : 'GET');
        });

        $route = array_first($filteredRoutes);

        if (!$route instanceof Route) {
            return null;
        }

        return $route;
    }

    /**
     * Получить роут по имени
     *
     * @psalm-suppress PossiblyUnusedMethod
     */
    public static function getByName(string $name): Route|null
    {
        /**
         * @psalm-suppress UndefinedMagicMethod
         * @var array<string, Route> $routes
         */
        $routes = Routes::get();

        return $routes[$name] ?? null;
    }
}
