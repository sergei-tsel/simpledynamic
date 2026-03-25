<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Site\Routers;

class Web
{
    public static array $routes = [
        'welcome' => [
            'method' => 'GET',
            'path'   => '/',
            'action' => [\App\Infrastructure\Http\Site\Controllers\PageController::class, 'welcome'],
            'name'   => 'welcome',
        ],
    ];
}
