<?php

declare(strict_types=1);

namespace Simpledynamic\Integrations\Guzzle;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Simpledynamic\Base\View\ClientInterface;
use Simpledynamic\Services\Logging\Logger;

/**
 * Клиент для отправки представления с помощью Guzzle
 */
final class GuzzleClient implements ClientInterface
{
    public function __construct(
        public Client $client = new Client(),
    ) {}

    /**
     * Отправить представление синхронно
     *
     * @param array<array-key, array<array-key, string>|string> $headers
     */
    #[\Override]
    public function send(string $method, string $url, array $data, array $headers = []): ?ResponseInterface
    {
        $jsonData = json_encode($data);

        if ($jsonData === false) {
            throw new \InvalidArgumentException('Failed to encode data to JSON');
        }

        $request = new Request(method: $method, uri: $url, headers: $headers, body: $jsonData);

        try {
            return $this->client->send($request);
        } catch (\Throwable $exception) {
            // Метод возвращает null при недоступности сервиса, поэтому сетевой сбой
            // должен оставаться в логе, иначе вызывающий не отличит его от пустого ответа
            Logger::report($exception);

            return null;
        }
    }

    /**
     * Отправить представление асинхронно
     *
     * @param array<array-key, array<array-key, string>|string> $headers
     */
    #[\Override]
    public function sendAsync(
        string $method,
        string $url,
        array $data,
        array $headers = [],
        ?bool $unwrap = null,
    ): ?ResponseInterface {
        $jsonData = json_encode($data);

        if ($jsonData === false) {
            throw new \InvalidArgumentException('Failed to encode data to JSON');
        }

        $request = new Request(method: $method, uri: $url, headers: $headers, body: $jsonData);

        try {
            /** @var ResponseInterface|null */
            return $unwrap === null
                ? $this->client->sendAsync($request)
                : $this->client->sendAsync($request)->wait(unwrap: $unwrap);
        } catch (\Throwable $exception) {
            Logger::report($exception);

            return null;
        }
    }
}
