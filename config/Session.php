<?php

declare(strict_types=1);

namespace config;

/**
 * Конфигурация переменных сессии
 *
 * @api
 * @psalm-suppress ClassCanBeFinal
 */
class Session extends Config
{
    /**
     * @var array<string, array<array-key, mixed>|scalar|null>
     * @psalm-suppress InvalidAttribute
     */
    #[\Override]
    protected static array  $local    = [
        'options' => [],
        'cookies' => [],
        'params'  => [],
    ];

    /**
     * @psalm-suppress InvalidAttribute
     */
    #[\Override]
    protected static string $filename = '';

    /**
     * Установить переменные сессии
     */
    public static function set(?string $login = null): void
    {
        self::setConfigParts([
            'cookies' => function (array $cookies): void {
                /** @var array<string, array{domain?: string|null, httponly?: bool|null, lifetime?: int|null, path?: string|null, samesite?: string|null, secure?: bool|null}|string> $cookies */
                foreach ($cookies as $key => $value) {
                    if (is_array($value)) {
                        if ($key === 'options') {
                            session_set_cookie_params($value);
                        } elseif (!empty($value)) {
                            session_set_cookie_params(array_replace(session_get_cookie_params(), $value));
                        }
                    }
                }
            },
            'options' => function (array $options): void {
                session_start(array_filter($options, is_scalar(...)));
            },
            'params'  => function (array $params): void {
                /** @var array<string, string> $params */
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
