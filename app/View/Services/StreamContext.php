<?php

declare(strict_types=1);

namespace App\View\Services;

/**
 * Сервис отправки представления с помощью потока
 */
class StreamContext
{
    /**
     * Отправить представление
     */
    public function send(
        string $method,
        string $url,
        array  $data,
        array  $headers = [],
    ): void {
        $options = [
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headers),
            ],
        ];

        $context = stream_context_create($options);

        file_put_contents($url, $data, LOCK_EX, $context);
    }
}
