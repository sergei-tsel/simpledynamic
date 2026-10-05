<?php

declare(strict_types=1);

namespace Simpledynamic\Services\CLI;

/**
 * Сервис для использования командной строки
 */
final class CommandLineManager
{
    public function __construct(
        private readonly ArgsResolver $argsResolver = new ArgsResolver(),
    ) {}

    /**
     * Получить аргументы, переданные в командной строке
     *
     * @param list<Option> $options Ожидаемые опции
     * @return array<string, array<int, bool>|bool|string|null>|array{message: string} Список значений опций
     */
    public function getArgs(array $options): array
    {
        return $this->argsResolver->resolve(options: $options);
    }

    /**
     * Получить список команд
     *
     * @param array<array-key, class-string<Command>> $commands
     * @throws \ReflectionException
     */
    public function echoList(array $commands): void
    {
        if ($commands === []) {
            return;
        }

        foreach ($commands as $name => $command) {
            $name = (string) $name;

            echo PHP_EOL;
            echo $this->getSignature(name: $name, command: $command) . PHP_EOL;
            echo $command::getDescription() . PHP_EOL;
            echo PHP_EOL;
        }
    }

    /**
     * Получить справку по командe
     *
     * @param class-string<Command> $command
     * @throws \ReflectionException
     */
    public function echoHelp(string $name, string $command): void
    {
        echo $name . ' ' . PHP_EOL;

        foreach ($command::getOptions() as $option) {
            echo $option->long . ', ' . $option->short . ' ' . $option->description . PHP_EOL;
        }

        echo $command::getDescription() . PHP_EOL;
    }

    /**
     * Получить сигнатуру команды со списком её опций
     *
     * @param class-string<Command> $command
     * @throws \ReflectionException
     */
    private function getSignature(string $name, string $command): string
    {
        $signature = $name . ' ';

        foreach ($command::getOptions() as $option) {
            $signature .= $option->long . '|' . $option->short . ' ';
        }

        return $signature;
    }
}
