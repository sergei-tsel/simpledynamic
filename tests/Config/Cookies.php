<?php

declare(strict_types=1);

namespace Test\Config;

use Simpledynamic\Services\Configuration\Config;

/**
 * Конфигурация куки
 */
class Cookies extends Config
{
    /**
     * @var array<string, array<array-key, mixed>|scalar|null>
     */
    #[\Override]
    protected static array $local = [];

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

        self::setConfig(static function (array $config): void {
            /** @var scalar|array{value: scalar, options?: array<array-key, mixed>}|null $item */
            foreach ($config as $key => $item) {
                if (is_string($item)) {
                    setcookie(name: (string) $key, value: $item);

                    continue;
                }

                if (!is_array($item)) {
                    continue;
                }

                setcookie(
                    (string) $key,
                    (string) $item['value'],
                    is_array($item['options'] ?? null) ? $item['options'] : [],
                );
            }
        });
    }
}
