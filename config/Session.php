<?php

declare(strict_types=1);

namespace config;

/**
 * Конфигурация переменных сессии
 */
class Session extends Config
{
    #[\Override]
    protected static array  $local    = [
        'options' => [],
    ];

    #[\Override]
    protected static string $filename = '';

    /**
     * Установить переменные сессии
     */
    public static function set(?string $login = null): void
    {
        self::setConfigParts([
            'cookies' => function (array $cookies): void {
                foreach ($cookies as $key => $value) {
                    $key === 'options' ? session_set_cookie_params($value) : session_set_cookie_params(...$value);
                }
            },
            'options' => function (array $options): void {
                session_start($options);
            },
            'params'  => function (array $params): void {
                foreach ($params as $key => $value) {
                    $_SESSION[$key] = $value;
                }
            },
        ]);

        if ($login) {
            $_SESSION['login'] = $login;
        }
    }
}
