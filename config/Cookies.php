<?php

declare(strict_types=1);

namespace config;

/**
 * Конфигурация куки
 */
class Cookies extends Config
{
    /**
     * @psalm-suppress InvalidAttribute
     */
    #[\Override]
    protected static array  $local    = [];

    /**
     * @psalm-suppress InvalidAttribute
     */
    #[\Override]
    protected static string $filename = '';

    /**
     * Установить куки
     */
    public static function set(?string $session = null): void
    {
        if ($session) {
            setcookie('session', Auth::hash('base', $session));
        }

        self::setConfig(function (array $config): void {
            foreach ($config as $key => $value) {
                if (is_string($value)) {
                    setcookie($key, $value);
                } elseif (is_array($value)) {
                    setcookie(...$value);
                }
            }
        });
    }
}
