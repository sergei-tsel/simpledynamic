<?php

declare(strict_types=1);

namespace config;

use App\Controller\Controllers\PageController;
use App\Controller\Routes\Route;

/**
 * Конфигурация роутов
 */
class Routes extends Config
{
    protected static array  $local    = [
        'welcome' => [
            'method'     => 'GET',
            'path'       => '/',
            'controller' => PageController::class,
            'action'     => 'welcome',
            'name'       => 'welcome',
        ],
    ];

    protected static string $filename = '';

    /**
     * Получить роуты
     */
    #[\Override]
    public static function getConfig(): array
    {
        if (self::$filename) {
            /** @var array $config */
            $config = array_merge(
                self::$local,
                yaml_parse_file(self::$filename),
            );
        } else {
            $config = self::$local;
        }

        return self::dot($config);
    }

    /**
     * Перевести глубину роутов в точечную нотацию
     */
    private static function dot(array $config): array
    {
        $routes = [];
        $groups = [];

        foreach ($config as $key => $value) {
            self::dotRoute(
                $value,
                $key,
                $routes,
                $groups,
            );
        }

        for ($i = 1; count($groups[$i - 1] ?? []) > 0; $i++) {
            foreach ($groups[$i - 1] as $name => $group) {
                self::dotRoute(
                    $group,
                    $name,
                    $routes,
                    $groups,
                    $i,
                );
            }
        }

        return $routes;
    }

    /**
     * Создать уровень роутов в точечной нотации
     */
    private static function dotRoute(
        array  $value,
        string $key,
        array  &$routes,
        array  &$groups,
        int    $level = 0
    ): void {
        if (array_key_exists('path', $value)) {
            $value = new Route(
                $value['method'],
                $value['path'],
                $value['controller'],
                $value['action'],
                $value['name'],
                $value['middlewares'] ?? [],
            );
        }

        if (is_array($value)) {
            foreach ($value as $name => $item) {
                $groups[$level][$key . '.' . $name] = $item;
            }
        } elseif ($value instanceof Route && ! array_key_exists($key, $routes)) {
            $routes[$value->getName()] = $value;
        }
    }

    /**
     * Получить параметры пути
     */
    public static function getPathParams(): array|null
    {
        $route = self::getByPath();

        $routePath = preg_filter(['#\{/u', '/}#u'], ['(?P<', '>\d+)'], $route->getPath());
        $routePath = '#^' . $routePath . '$#';

        $params = [];

        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        mb_ereg($routePath, $path, $params);

        return $params;
    }

    /**
     * Получить роут по пути
     */
    public static function getByPath(): Route|null
    {
        $config = Routes::getConfig();

        $routes = array_filter($config, function (Route $route) {
            $routePath = preg_replace('#\{/d+}#u', '\d+', $route->getPath());
            $routePath = '#^' . $routePath . '$#';

            $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

            return mb_ereg_match($routePath, $path) && $route->getMethod() === $_SERVER['REQUEST_METHOD'] ?? 'GET';
        });

        return $routes[0] ?? null;
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
