<?php

declare(strict_types=1);

namespace Test\App\Http\Routes;

use Simpledynamic\Services\Routing\Middleware;
use Test\App\Http\Controllers\TestController;
use Test\App\Http\Middlewares\RouteGroupTest;

/**
 * Тестовая группа роутов
 *
 * Ключи роутов хранят путь без ключа группы: группа 'test' сопоставляется с
 * префиксом пути '/test', который задаётся ключом группы в конфигурации роутов.
 */
#[Middleware(RouteGroupTest::class)]
class Test
{
    /**
     * Получить роуты
     *
     * Порядок важен: маршрут с PATH-параметром объявлен последним, чтобы не
     * перехватывать более конкретные пути.
     *
     * @return array<string, array{method: string, action: array<string, string>|string}>
     */
    public static function getRoutes(): array
    {
        return [
            '/' => [
                'method' => 'GET',
                'action' => [
                    'class' => TestController::class,
                    'method' => 'root',
                ],
            ],
            '/welcome' => [
                'method' => 'GET',
                'action' => [
                    'class' => TestController::class,
                    'method' => 'welcome',
                ],
            ],
            '/greeting/{name}' => [
                'method' => 'GET',
                'action' => [
                    'class' => TestController::class,
                    'method' => 'greeting',
                ],
            ],
            '/{pathParamName}' => [
                'method' => 'GET',
                'action' => [
                    'class' => TestController::class,
                    'method' => 'pathParam',
                ],
            ],
        ];
    }
}
