<?php

declare(strict_types=1);

namespace config;

/**
 * Конфигурация заголовков
 *
 * @api
 * @psalm-suppress ClassCanBeFinal
 */
class Headers extends Config
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
     * Установить заголовки
     */
    public static function set(): void
    {
        self::setConfig(
            function (array $config): void {
                /** @var array|scalar $value */
                foreach ($config as $key => $value) {
                    if (is_string($value)) {
                        header($value);
                    } elseif (is_array($value)) {
                        header(
                            header: (string) ($value['header'] ?? $key),
                            replace: (bool) ($value['replace'] ?? true)
                        );
                    }
                }
            }
        );
    }
}
