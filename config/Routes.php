<?php

declare(strict_types=1);

namespace config;

use Sympledynamic\Base\Controller\Route;
use Sympledynamic\Gateway\Templating\TwigView;
use Sympledynamic\Services\Arrays\NotationManager;
use Uri\Rfc3986\Uri;

/**
 * Конфигурация роутов
 *
 * @psalm-suppress ClassCanBeFinal
 */
class Routes extends Config
{
    /**
     * @psalm-suppress InvalidAttribute
     */
    #[\Override]
    protected static array  $local    = [
        'base' => 'http://localhost:8000/',
    ];

    /**
     * @psalm-suppress InvalidAttribute
     */
    #[\Override]
    protected static string $filename = '';

    protected static array $routers   = [];

    protected static ?Uri $uri = null;

    /**
     * Получить роуты
     */
    public static function get(): array
    {
        $routes = [
            'welcome' => [
                'method' => 'GET',
                'path'   => '/',
                'action' => function (): void {
                    echo new TwigView('welcome.php.twig')->render();
                },
            ],
        ];

        if (self::$routers !== []) {
            foreach (self::$routers as $router) {
                if (!class_exists($router)) {
                    continue;
                }

                $routes = array_merge(
                    $routes,
                    $router::getRoutes(),
                );
            }
        }

        return $routes
            |> NotationManager::instanceOne('.')->fromMdsArray(...)
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
        $routes = self::get();

        $filteredRoutes = array_filter($routes, function (Route $route): bool {
            $routePath = preg_replace('#\{/d+}#u', '\d+', $route->getPath());
            $routePath = '^' . $routePath . '$';

            $path = self::getUri()->getPath();

            return mb_ereg_match($routePath, $path) && $route->getMethod() === ($_SERVER['REQUEST_METHOD'] ?? 'GET');
        });

        return array_first($filteredRoutes);
    }

    /**
     * Получить роут по имени
     *
     * @psalm-suppress PossiblyUnusedMethod
     */
    public static function getByName(string $name): Route|null
    {
        $routes = self::get();

        return $routes[$name] ?? null;
    }
}
