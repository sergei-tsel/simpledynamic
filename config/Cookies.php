<?php

declare(strict_types=1);

namespace config;

/**
 * Конфигурация куки
 *
 * @api
 * @psalm-suppress ClassCanBeFinal
 */
class Cookies extends Config
{
    /**
     * @var array<string, array<array-key, mixed>|scalar|null>
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
        if ($session !== null) {
            setcookie('session', Auth::hash('base', $session));
        }

        self::setConfig(
            function (array $config): void {
                /** @var array|scalar $item */
                foreach ($config as $key => $item) {
                    if (is_string($item)) {
                        setcookie(name: (string) $key, value: $item);
                    } elseif (is_array($item)) {
                        setcookie((string) $key, (string) $item['value'], (array) $item['options']);
                    }
                }
            }
        );
    }
}
