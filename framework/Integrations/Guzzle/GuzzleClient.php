<?php

declare(strict_types=1);

namespace Simpledynamic\Integrations\Guzzle;

use GuzzleHttp\Client as GazzleHttpClient;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Simpledynamic\Base\View\Client;

/**
 * Клиент для отправки представления с помощью Guzzle
 *
 * @psalm-suppress UnusedClass
 */
final class GuzzleClient extends Client
{
    public function __construct(
        public GazzleHttpClient $client = new GazzleHttpClient(),
    ) {
    }

    /**
     * Отправить представление
     *
     * @param array<array-key, array<array-key, string>|string> $headers
     */
    public function send(
        string $method,
        string $url,
        array  $data,
        array  $headers = [],
        bool   $isAsync = false,
        ?bool  $unwrap  = null,
    ): ResponseInterface|PromiseInterface|null {
        $jsonData = json_encode($data);

        if ($jsonData === false) {
            throw new \InvalidArgumentException('Failed to encode data to JSON');
        }

        $request = new Request(method: $method, uri: $url, headers: $headers, body: $jsonData);

        try {
            if ($isAsync) {
                /** @psalm-suppress MixedReturnStatement */
                return $unwrap !== null
                    ? $this->client->sendAsync($request)->wait(unwrap: $unwrap)
                    : $this->client->sendAsync($request);
            } else {
                return $this->client->send($request);
            }
        } catch (\Throwable) {
            return null;
        }
    }
}
