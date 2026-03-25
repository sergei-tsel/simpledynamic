<?php

declare(strict_types=1);

namespace config;

use App\Framework\Services\Arrays\DotNotationManager;
use App\Framework\Services\Routing\Route;
use Uri\Rfc3986\Uri;

/**
 * Конфигурация роутов
 */
class Routes extends Config
{
    protected static array  $local    = [
        'base' => 'http://localhost:8000/',
    ];

    protected static string $filename = '';

    protected static array $routers   = [
        'App\Infrastructure\Http\Site\Routers\Web',
    ];

    protected static ?Uri $uri = null;

    /**
     * Получить роуты
     *
     * @return Route[]
     */
    #[\Override]
    public static function getConfig(): array
    {
        if (self::$filename) {
            $config = array_merge(
                self::$local,
                yaml_parse_file(self::$filename),
            );
        } else {
            $config = self::$local;
        }

        if (self::$routers !== []) {
            foreach (self::$routers as $router) {
                if (!class_exists($router)) {
                    continue;
                }

                $config = array_merge(
                    $config,
                    $router::${'routes'},
                );
            }
        }

        return $config
            |> new DotNotationManager()->fromMdsArray(...)
            |> Route::instanceMany(...);
    }

    /**
     * Получить Uri
     */
    public static function getUri(): Uri
    {
        if (self::$uri === null) {
            self::$uri = new Uri(uri: $_SERVER['REQUEST_URI'], baseUrl: new Uri(self::$local['base']));
        }

        return self::$uri;
    }

    /**
     * Получить параметры пути
     */
    public static function getPathParams(): array|null
    {
        $route = self::getByPath();

        $routePath = preg_filter(['#\{/u', '/}#u'], ['(?P<', '>\d+)'], $route->getPath());
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
        $config = self::getConfig();

        $routes = array_filter($config, function (Route $route) {
            $routePath = preg_replace('#\{/d+}#u', '\d+', $route->getPath());
            $routePath = '^' . $routePath . '$';

            $path = self::getUri()->getPath();

            return mb_ereg_match($routePath, $path) && $route->getMethod() === $_SERVER['REQUEST_METHOD'] ?? 'GET';
        });

        return array_first($routes);
    }

    /**
     * Получить роут по имени
     */
    public static function getByName(string $name): Route|null
    {
        $routes = self::getConfig();

        return $routes[$name] ?? null;
    }
}
