<?php
declare(strict_types=1);
namespace App\Controller\Routes;

/**
 * Роутер
 */
class Router
{
    /**
     * Обработать запрос
     */
    public static function handle(): void
    {
        $path  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $routes = (new ParamsFilter())->getFilteredData([
            ParamTypes::CONFIG->value => [
                "rout" => [
                    "$path" => FILTER_DEFAULT,
                ],
            ],
        ]);

        if (count($routes) > 0) {
            $routes = array_filter($routes, function ($route) {
                return $route->getMethod() === $_SERVER['REQUEST_METHOD'];
            });

            if (count($routes) === 1) {
                /** @var Route $route */
                $route = array_shift($routes);

                $route->callAction();
            }
        }
    }
}
