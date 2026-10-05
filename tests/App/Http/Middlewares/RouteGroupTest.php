<?php

declare(strict_types=1);

namespace Test\App\Http\Middlewares;

/**
 * Тестовый мидлвар для группы роутов
 *
 * Мидлвар группы вызывается до определения роута, поэтому его параметры не
 * попадают в request: признак обработки передаётся заголовком ответа.
 */
class RouteGroupTest
{
    /**
     * Заголовок, которым мидлвар сообщает об обработке запроса
     */
    public const string HEADER = 'X-Route-Group';

    /**
     * Обработать запрос
     *
     * @param array<string, mixed> $request Внешние параметры
     * @return array<string, mixed>
     */
    public function handle(array $request): array
    {
        header(self::HEADER . ': test; params=' . count($request));

        return [];
    }
}
