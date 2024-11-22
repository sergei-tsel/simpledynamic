<?php

declare(strict_types=1);

namespace App\Controller\Routes;

use config\Routes;

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
        $route = Routes::getByPath();

        if ($route instanceof Route) {
            $route->callAction();
        }
    }
}
