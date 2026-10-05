<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Auth;

use Simpledynamic\Services\Configuration\Auth;
use Simpledynamic\Services\Configuration\Cookies;
use Simpledynamic\Services\Configuration\Session;
use Simpledynamic\Services\Routing\UriBuilder;

/**
 * Сервис для управления авторизацией
 */
final class Authenticator
{
    /**
     * Авторизоваться через браузер
     *
     * @param array{SERVER: array{PHP_AUTH_USER: string, PHP_AUTH_PW: string}} $params
     * @return array<string, string>
     */
    public function browse(array $params, #[\SensitiveParameter] string $hashedPassword): array
    {
        $path = explode('/', new UriBuilder()->buildRequestUri()->getPath());

        $clientRealm = mb_ucfirst($path[1] ?? '');

        /**
         * @var string[] $realms
         */
        $realms = Auth::getConfigPart('realms');

        if (!array_key_exists('PHP_AUTH_USER', $params['SERVER']) || !in_array($clientRealm, $realms, true)) {
            return $this->tryThrow(message: '401' . PHP_EOL . 'Логин не передан', headers: [
                'HTTP/1.1 401 Unauthorized',
                "WWW-Authenticate: Basic realm=\`{$clientRealm}\`",
            ]);
        }

        if (!password_verify($params['SERVER']['PHP_AUTH_PW'], $hashedPassword)) {
            return $this->tryThrow(message: '403' . PHP_EOL . 'Логин или пароль неправильный', headers: [
                'HTTP/1.1 403 Forbidden',
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
     * @return array<string, string>
     */
    public function form(array $params, #[\SensitiveParameter] string $hashedPassword): array
    {
        /**
         * @var string $hash
         */
        $hash = Auth::hash('base', $this->getSessionId());

        /**
         * @var array{hash: string, login: string} $sessionCookies
         */
        $sessionCookies = Session::getConfigPart('cookies');

        if (hash_equals($hash, $sessionCookies['hash'])) {
            return [
                'login' => $sessionCookies['login'],
            ];
        }

        if (!password_verify($params['POST']['password'], $hashedPassword)) {
            return $this->tryThrow('403' . PHP_EOL . 'Логин или пароль неправильный');
        }

        Session::set();

        Cookies::set($this->getSessionId());

        return [
            'login' => $params['POST']['login'],
        ];
    }

    /**
     * Авторизоваться через дайджест
     *
     * @param array{POST: array{secret: string, key: string}} $params
     * @return array{digest: array{secret: string, key: string}}
     */
    public function digest(array $params): array
    {
        /**
         * @var string[] $secrets
         */
        $secrets = Auth::getConfigPart('secrets');

        if (!hash_equals($secrets[$params['POST']['secret']] ?? '', $params['POST']['key'])) {
            // Объединение нужно из-за более узкого возвращаемого типа у tryThrow:
            // пустой массив не удовлетворяет array{secret: string, key: string},
            // поэтому недостающие ключи дописываются исходными значениями
            return [
                'digest' => $this->tryThrow('403' . PHP_EOL . 'Секретный ключ неправильный')
                    + [
                        'secret' => $params['POST']['secret'],
                        'key' => $params['POST']['key'],
                    ],
            ];
        }

        return [
            'digest' => [
                'secret' => $params['POST']['secret'],
                'key' => $params['POST']['key'],
            ],
        ];
    }

    /**
     * Получить идентификатор доступа
     */
    private function getSessionId(): string
    {
        $sessionId = session_id();

        if ($sessionId === false) {
            $this->tryThrow('403' . PHP_EOL . 'Ошибка при получении идентификатора доступа');

            // Метод объявлен как string, а tryThrow возвращает array{}, поэтому
            // возвращается пустая строка: вызывающий получит осмысленный отказ,
            // а session_id() больше не будет выдаваться за валидную строку
            return '';
        }

        return $sessionId;
    }

    /**
     * Выбросить исключение в try-catch-finally
     *
     * Метод ничего не бросит наружу: исключение перехватывается, а заголовки
     * отправляются в finally. Пустой массив — сигнал вызывающему прекратить
     * обработку, поэтому возвращаемое значение обязательно нужно проверять.
     *
     * @param array<array-key, non-empty-string> $headers
     * @return array{}
     */
    private function tryThrow(string $message, array $headers = []): array
    {
        try {
            throw new \Exception($message);
        } catch (\Exception) {
            // Ожидаемый отказ авторизации, а не сбой: сообщение уже отправлено
            // клиенту в finally, запись в лог была бы шумом на каждый 401 и 403
            return [];
        } finally {
            if ($headers !== []) {
                foreach ($headers as $header) {
                    header($header);
                }
            }
        }
    }
}
