<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Site\Routers;

use App\Infrastructure\Http\Site\Controllers\PageController;

class Web
{
    public static array $routes = [
        'welcome' => [
            'method' => 'GET',
            'path'   => '/',
            'action' => [PageController::class, 'welcome'],
        ],
    ];
}
