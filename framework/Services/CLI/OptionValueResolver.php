<?php

declare(strict_types=1);

namespace Simpledynamic\Services\CLI;

/**
 * Разбор значения одной опции
 *
 * Значение указывается через '=' или следующим аргументом, если он не начинается
 * с дефиса.
 */
final class OptionValueResolver
{
    public function __construct(
        private readonly FlagsResolver $flagsResolver = new FlagsResolver(),
    ) {}

    /**
     * Получить значение опции из аргумента
     *
     * @param array<int|string, string> $argv Аргументы командной строки
     * @return array<int, bool>|bool|string|null Значение опции
     */
    public function resolve(Option $option, string $arg, array $argv, int $index): array|bool|string|null
    {
        $parts = explode('=', $arg);
        $nextArg = $argv[$index + 1] ?? null;

        if (count($parts) === 1 && $nextArg !== null && !str_starts_with($nextArg, '-')) {
            $parts[1] = $nextArg;
        }

        return $parts[1] ?? $this->searchInArg(option: $option, arg: $parts[0]);
    }

    /**
     * Найти значение опции в аргументе
     *
     * @return array<int, bool>|bool|string|null
     */
    private function searchInArg(Option $option, string $arg): array|bool|string|null
    {
        if ($this->flagsResolver->isCluster(option: $option, arg: $arg)) {
            return $this->flagsResolver->resolve(option: $option, arg: $arg);
        }

        $separator = $this->getSeparator(option: $option, arg: $arg);

        if ($separator === null) {
            return null;
        }

        $value = explode($separator, $arg)[1] ?? null;

        return $value !== null && $value !== ' ' ? $value : null;
    }

    /**
     * Получить разделитель значения опции в аргументе
     */
    private function getSeparator(Option $option, string $arg): ?string
    {
        if ($option->long !== '' && str_starts_with($arg, '--')) {
            return $option->long;
        }

        if ($option->short !== '' && str_starts_with($arg, '-')) {
            return $option->short;
        }

        return null;
    }
}
