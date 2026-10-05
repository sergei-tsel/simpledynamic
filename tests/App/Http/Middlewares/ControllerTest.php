<?php

declare(strict_types=1);

namespace Test\App\Http\Middlewares;

/**
 * Тестовый мидлвар для контроллера
 *
 * Кроме признака обработки возвращает число переданных параметров: так видно,
 * что мидлвар получает накопленные внешние параметры.
 */
class ControllerTest
{
    /**
     * Обработать запрос
     *
     * @param array<string, mixed> $request Внешние параметры
     * @return array<string, mixed>
     */
    public function handle(array $request): array
    {
        return [
            'controller' => true,
            'controllerParams' => count($request),
        ];
    }
}
