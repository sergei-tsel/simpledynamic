<?php

declare(strict_types=1);

namespace config;

use Simpledynamic\Integrations\Twig\TwigView;
use Simpledynamic\Services\Filtration\Sanitization\SanitizationFilter;
use Simpledynamic\Services\Filtration\Validation\ValidationFilter;
use Sympledynamic\Base\Controller\Route;
use Sympledynamic\Services\Arrays\NotationManager;
use Sympledynamic\Services\Filtration\Filter;
use Sympledynamic\Services\Filtration\FilterArgument;
use Sympledynamic\Services\Filtration\FilterParam;
use Sympledynamic\Services\Routing\InputType;
use Uri\Rfc3986\Uri;

/**
 * Конфигурация роутов
 *
 * @api
 * @psalm-suppress ClassCanBeFinal
 */
class Routes extends Config
{
    /**
     * @var array<string, array<array-key, mixed>|scalar|null>
     * @psalm-suppress InvalidAttribute
     */
    #[\Override]
    protected static array  $local    = [
        'base' => 'http://localhost:8000',
    ];

    /**
     * @psalm-suppress InvalidAttribute
     */
    #[\Override]
    protected static string $filename = '';

    /**
     * @var string[]
     */
    protected static array $routers   = [];

    protected static ?Uri $uri = null;

    /**
     * Получить роуты
     *
     * @return Route[]
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
                if (class_exists($router) && method_exists($router, 'getRoutes')) {
                    /**
                     * @psalm-suppress MixedArgument
                     * @psalm-suppress MixedMethodCall
                     */
                    $routes = array_merge(
                        $routes,
                        $router::getRoutes(),
                    );
                }
            }
        }

        return Route::instanceMany(
            NotationManager::instanceOne('.')->fromMdsArray($routes)
        );
    }

    /**
     * Получить Uri
     */
    public static function getUri(): Uri
    {
        /** @var string $base */
        $base = self::getConfigPart('base');

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

        return self::$uri ??= new Uri(uri: is_string($validatedUrl) ? $validatedUrl : '/', baseUrl: new Uri($base));
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
        $routes = self::get();

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
        $routes = self::get();

        return $routes[$name] ?? null;
    }
}
