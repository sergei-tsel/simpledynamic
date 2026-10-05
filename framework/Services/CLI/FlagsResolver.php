<?php

declare(strict_types=1);

namespace Simpledynamic\Services\CLI;

/**
 * Разбор набора коротких флагов
 *
 * Флаги одной опции можно передать слитно: -abc для опции -a.
 */
final class FlagsResolver
{
    /**
     * Проверить, является ли аргумент набором коротких флагов слитно
     */
    public function isCluster(Option $option, string $arg): bool
    {
        return $option->type === OptionType::FLAG && str_starts_with($arg, '-') && !str_starts_with($arg, '--');
    }

    /**
     * Получить значение набора коротких флагов
     *
     * @return array<int, bool>|bool|null
     */
    public function resolve(Option $option, string $arg): array|bool|null
    {
        $count = max(0, mb_substr_count($arg, mb_trim($option->short, '-')));

        return match (true) {
            $count === 0 => null,
            $count === 1 => true,
            default => array_fill(0, $count, true),
        };
    }
}
