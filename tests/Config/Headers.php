<?php

declare(strict_types=1);

namespace Test\Config;

use Simpledynamic\Services\Configuration\Config;

/**
 * Конфигурация заголовков
 */
class Headers extends Config
{
    /**
     * @var array<string, array<array-key, mixed>|scalar|null>
     */
    #[\Override]
    protected static array $local = [];

    #[\Override]
    protected static string $filename = '';

    /**
     * Установить заголовки
     */
    public static function set(): void
    {
        self::setConfig(static function (array $config): void {
            /** @var scalar|array{header?: scalar, replace?: scalar}|null $value */
            foreach ($config as $key => $value) {
                if (is_string($value)) {
                    header($value);

                    continue;
                }

                if (!is_array($value)) {
                    continue;
                }

                $replace = $value['replace'] ?? true;

                header(header: (string) ($value['header'] ?? $key), replace: !is_bool($replace) || $replace);
            }
        });
    }
}
