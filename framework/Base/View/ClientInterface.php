<?php

declare(strict_types=1);

namespace Simpledynamic\Base\View;

/**
 * Клиент
 */
interface ClientInterface
{
    /**
     * Отправить синхронно
     *
     * @param array $data Данные
     * @param array<string[]|string> $headers Заголовки
     */
    public function send(string $method, string $url, array $data, array $headers = []): ?object;

    /**
     * Отправить асинхронно
     *
     * @param array $data Данные
     * @param array<string[]|string> $headers Заголовки
     */
    public function sendAsync(
        string $method,
        string $url,
        array $data,
        array $headers = [],
        ?bool $unwrap = null,
    ): ?object;
}
