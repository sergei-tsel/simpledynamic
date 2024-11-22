<?php

declare(strict_types=1);

namespace App\View\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

/**
 * Сервис отправки представления с помощью Guzzle
 */
class Guzzle
{
    public function __construct(
        public Client $client = new Client(),
    ) {
    }

    /**
     * Отправить представление */
    public function send(
        string $method,
        string $url,
        array  $data,
        array  $headers = [],
        bool   $isAsync = false
    ): ResponseInterface|PromiseInterface {
        $request = new Request($method, $url, $headers, $data);

        if ($isAsync) {
            return $this->client->sendAsync($request)->wait();
        } else {
            return $this->client->send($request);
        }
    }
}
