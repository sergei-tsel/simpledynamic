<?php

declare(strict_types=1);

namespace Simpledynamic\Services\CLI;

/**
 * Разбор опций команды
 *
 * Находит в аргументах командной строки значения ожидаемых опций и передаёт их
 * разбору значения отдельной опции.
 */
final class ArgsResolver
{
    public function __construct(
        private readonly OptionValueResolver $optionValueResolver = new OptionValueResolver(),
        private readonly FlagsResolver $flagsResolver = new FlagsResolver(),
    ) {}

    /**
     * Разобрать значения опций команды
     *
     * @param list<Option> $options Ожидаемые опции
     * @return array<string, array<int, bool>|bool|string|null>|array{message: string} Список значений опций
     */
    public function resolve(array $options): array
    {
        if ($options === []) {
            return [];
        }

        $argv = $this->getArgv();
        $args = [];

        foreach ($options as $option) {
            foreach ($this->matchArgs(option: $option, argv: $argv) as $index => $arg) {
                $value = $this->optionValueResolver->resolve(
                    option: $option,
                    arg: $arg,
                    argv: $argv,
                    index: (int) $index,
                );

                if ($option->type === OptionType::REQUIRED && $value === null) {
                    return $this->toMessage(option: $option);
                }

                $args[mb_trim($option->long, '--')] = $value;
            }
        }

        return $args;
    }

    /**
     * Найти в аргументах командной строки те, что относятся к опции
     *
     * @param array<int|string, string> $argv Аргументы командной строки
     * @return array<int|string, string> Совпавшие аргументы с их индексами
     */
    private function matchArgs(Option $option, array $argv): array
    {
        $matched = [];

        foreach ($argv as $index => $arg) {
            if ($index < 2 || !$this->isOptionArg(option: $option, arg: $arg)) {
                continue;
            }

            $matched[$index] = $arg;
        }

        return $matched;
    }

    /**
     * Проверить, относится ли аргумент к опции
     */
    private function isOptionArg(Option $option, string $arg): bool
    {
        if (str_starts_with($arg, $option->long) || str_starts_with($arg, $option->short)) {
            return true;
        }

        // Набор коротких флагов слитно: -abc для опции -a
        return (
            $this->flagsResolver->isCluster(option: $option, arg: $arg)
            && mb_strpos($arg, mb_trim($option->short, '-')) !== false
        );
    }

    /**
     * Получить аргументы командной строки
     *
     * @return array<int|string, string> Аргументы командной строки
     */
    private function getArgv(): array
    {
        /** @var array<int|string, mixed> $argv */
        $argv = $_SERVER['argv'];

        return array_filter($argv, is_string(...));
    }

    /**
     * Получить сообщение о не переданной обязательной опции
     *
     * Ключ message зарезервирован командой под ошибку.
     *
     * @return array{message: string}
     */
    private function toMessage(Option $option): array
    {
        return [
            'message' => 'Не передана обязательная опция ' . mb_trim($option->long, '--'),
        ];
    }
}
