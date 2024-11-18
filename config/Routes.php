<?php
declare(strict_types=1);
namespace config;

use App\Controller\Routes\Route;

/**
 * Конфигурация роутов
 */
class Routes extends Config
{
    protected static array $local     = [];
    protected static string $filename = '';

    /**
     * Получить роуты
     */
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

        $recursion = function (Route|array $value, string $key, int $level = 0) use (&$routes, &$groups) {
            if (is_array($value)) {
                foreach ($value as $name => $item) {
                    $groups[$level][$key . '.' . $name] = $item;
                }
            } elseif ($value instanceof Route && !array_key_exists($key, $routes)) {
                $routes[$value->getName()] = $value;
            }
        };

        foreach ($config as $key => $value) {
            $recursion($value, $key);
        }

        for ($i = 1; count($groups[$i - 1] ?? []) > 0; $i++) {
            foreach ($groups[$i - 1] as $name => $group) {
                $recursion($group, $name, $i);
            }
        };

        return $routes;
    }

    /**
     * Получить параметры пути
     */
    public static function getPathParams(): array
    {
        $route = self::getByPath();

        $routePath = preg_filter(['#\{/u', '/}#u'], ['(?P<', '>\d+)'], $route->getPath());
        $routePath = '#^' . $routePath . '$#';

        $params = [];

        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
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

            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

            return mb_ereg_match($routePath, $path) && $route->getMethod() === $_SERVER['REQUEST_METHOD'];
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
