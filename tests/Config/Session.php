<?php

declare(strict_types=1);

namespace Test\Config;

use Simpledynamic\Services\Configuration\Config;

/**
 * Конфигурация переменных сессии
 */
class Session extends Config
{
    /**
     * @var array<string, array<array-key, mixed>|scalar|null>
     */
    #[\Override]
    protected static array $local = [
        'options' => [],
        'cookies' => [],
        'params' => [],
    ];

    #[\Override]
    protected static string $filename = '';

    /**
     * Установить переменные сессии
     */
    public static function set(?string $login = null): void
    {
        self::setConfigParts([
            'cookies' => static function (array $cookies): void {
                /**
                 * @var array<string, array{domain?: string|null, httponly?: bool|null, lifetime?: int|null, path?: string|null, samesite?: string|null, secure?: bool|null}|string> $cookies
                 * @var array{domain?: string|null, httponly?: bool|null, lifetime?: int|null, path?: string|null, samesite?: string|null, secure?: bool|null}|string $value
                 */
                foreach ($cookies as $key => $value) {
                    if (!is_array($value)) {
                        continue;
                    }

                    if ($key === 'options') {
                        session_set_cookie_params($value);

                        continue;
                    }

                    if ($value === []) {
                        continue;
                    }

                    session_set_cookie_params(array_replace(session_get_cookie_params(), $value));
                }
            },
            'options' => static function (array $options): void {
                session_start(array_filter($options, is_scalar(...)));
            },
            'params' => static function (array $params): void {
                /**
                 * @var array<string, string> $params
                 * @var string $value
                 */
                foreach ($params as $key => $value) {
                    $_SESSION[$key] = $value;
                }
            },
        ]);

        if ($login !== null) {
            $_SESSION['login'] = $login;
        }
    }
}
