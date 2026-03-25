<?php

declare(strict_types=1);

namespace config;

/**
 * Конфигурация куки сессии
 */
class SessionCookies extends Session
{
    #[\Override]
    protected static array  $local    = [];
    #[\Override]
    protected static string $filename = '';

    /**
     * Получить метод установки
     */
    protected static function getSetMethod(): callable
    {
        return function (string|int $key, array $value): void {
            if ($key === 'options') {
                session_set_cookie_params($value);
            } else {
                session_set_cookie_params(...$value);
            }
        };
    }

    /**
     * Установить куки сесcии
     */
    public static function setSessionCookies(): void
    {
        self::setConfig(self::getSetMethod());
    }
}
