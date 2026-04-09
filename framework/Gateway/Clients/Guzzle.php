<?php

declare(strict_types=1);

namespace Sympledynamic\Gateway\Clients;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

/**
 * Клиент для отправки представления с помощью Guzzle
 *
 * @psalm-suppress UnusedClass
 */
class Guzzle
{
    public function __construct(
        public Client $client = new Client(),
    ) {
    }

    /**
     * Отправить представление
     */
    public function send(
        string $method,
        string $url,
        array  $data,
        array  $headers = [],
        bool   $isAsync = false
    ): ResponseInterface|PromiseInterface {
        $request = new Request(method: $method, uri: $url, headers: $headers, body: json_encode($data));

        if ($isAsync) {
            return $this->client->sendAsync($request)->wait();
        } else {
            return $this->client->send($request);
        }
    }
}
