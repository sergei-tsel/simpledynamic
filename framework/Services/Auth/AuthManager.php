<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Auth;

use Simpledynamic\Base\Controller\Route;
use Simpledynamic\Services\Configuration\Auth;
use Simpledynamic\Services\Configuration\Cookies;
use Simpledynamic\Services\Configuration\Session;

/**
 * Сервис для управления авторизацией
 *
 * @psalm-suppress UnusedClass
 */
final class AuthManager
{
    /**
     * Авторизоваться через браузер
     *
     * @param array{SERVER: array{PHP_AUTH_USER: string, PHP_AUTH_PW: string}} $params
     */
    public function browse(array $params, string $hashedPassword): array
    {
        $path = explode('/', Route::getUri()->getPath());

        $clientRealm = mb_ucfirst($path[1]);

        /**
         * @psalm-suppress UndefinedMagicMethod
         * @var string[] $realms
         */
        $realms = Auth::getConfigPart('realms');

        if (!isset($params['SERVER']['PHP_AUTH_USER']) || !in_array($clientRealm, $realms)) {
            $this->tryThrow(message: "401" . PHP_EOL . "Логин не передан", headers: [
                "HTTP/1.1 401 Unauthorized",
                "WWW-Authenticate: Basic realm=\"$clientRealm\"",
            ]);
        }

        if (!password_verify($params['SERVER']['PHP_AUTH_PW'], $hashedPassword)) {
            $this->tryThrow(message: "403" . PHP_EOL . "Логин или пароль неправильный", headers: [
                "HTTP/1.1 403 Forbidden",
            ]);
        }

        return [
            'login' => $params['SERVER']['PHP_AUTH_USER'],
        ];
    }

    /**
     * Авторизоваться через форму
     *
     * @param array{POST: array{password: string, login: string}} $params
     */
    public function form(array $params, string $hashedPassword): array
    {
        /**
         * @psalm-suppress UndefinedMagicMethod
         * @var string $hash
         */
        $hash = Auth::hash('base', $this->getSessionId());

        /**
         * @psalm-suppress UndefinedMagicMethod
         * @var array{hash: string, login: string} $sessionCookies
         */
        $sessionCookies = Session::getConfigPart('cookies');

        if (hash_equals($hash, $sessionCookies['hash'])) {
            return [
                'login' => $sessionCookies['login'],
            ];
        }

        if (!password_verify($params['POST']['password'], $hashedPassword)) {
            $this->tryThrow("403" . PHP_EOL . "Логин или пароль неправильный");
        }

        /**
         * @psalm-suppress UndefinedMagicMethod
         */
        Session::set();

        /**
         * @psalm-suppress UndefinedMagicMethod
         */
        Cookies::set($this->getSessionId());

        return [
            'login' => $params['POST']['login'],
        ];
    }

    /**
     * Авторизоваться через дайджест
     *
     * @param array{POST: array{secret: string, key: string}} $params
     */
    public function digest(array $params): array
    {
        /**
         * @psalm-suppress UndefinedMagicMethod
         * @var string[] $secrets
         */
        $secrets = Auth::getConfigPart('secrets');

        if (!hash_equals($secrets[$params['POST']['secret']], $params['POST']['key'])) {
            $this->tryThrow("403" . PHP_EOL . "Секретный ключ неправильный");
        }

        return [
            'digest' => [
                'secret' => $params['POST']['secret'],
                'key'    => $params['POST']['key'],
            ],
        ];
    }

    /**
     * Получить идентификатор доступа
     */
    protected function getSessionId(): string
    {
        $sessionId = session_id();

        if ($sessionId === false) {
            $this->tryThrow("403" . PHP_EOL . "Ошибка при получении идентификатора доступа");
        }

        /** @var string $sessionId */
        return $sessionId;
    }

    /**
     * Выбросить исключение в try-catch-finally
     *
     * @param array<array-key, non-empty-string> $headers
     */
    protected function tryThrow(string $message, array $headers = []): array
    {
        try {
            throw new \Exception($message);
        } catch (\Throwable) {
        } finally {
            if ($headers !== []) {
                foreach ($headers as $header) {
                    header($header);
                }
            }

            return [];
        }
    }
}
