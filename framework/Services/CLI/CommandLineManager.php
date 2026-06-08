<?php

declare(strict_types=1);

namespace Simpledynamic\Services\CLI;

/**
 * Сервис для использования командной строки
 *
 * @api
 * @psalm-suppress ClassCanBeFinal
 */
final class CommandLineManager
{
    /**
     * Получить аргументы, переданные в командной строке
     *
     * @return array<bool[]|string|bool|null>|array{message: string}|null[]
     */
    public function getArgs(array $options): array
    {
        if ($options === [] || !isset($_SERVER['argc']) || !isset($_SERVER['argv'])) {
            return [];
        }

        $args = [];

        /** @var Option $option */
        foreach ($options as $option) {
            for ($i = 2; $i < $_SERVER['argc']; $i++) {
                /** @psalm-suppress RedundantCast */
                $arg = (string) $_SERVER['argv'][$i];

                if (!str_starts_with($arg, $option->long)
                    && !str_starts_with($arg, $option->short)
                    && (
                        $option->type === OptionType::FLAG
                        && str_starts_with($arg, '-')
                        && !str_starts_with($arg, '--')
                        && mb_strpos($arg, mb_trim($option->short, '-')) === false
                    )
                ) {
                    continue;
                }

                $matches = explode('=', $arg);
                /** @psalm-suppress RedundantCast */
                $nextArg = isset($_SERVER['argv'][$i + 1]) ? (string) $_SERVER['argv'][$i + 1] : null;

                if ((count($matches) === 1) && $nextArg !== null && !str_starts_with($nextArg, '-')) {
                    $matches[1] = $nextArg;
                }

                $value = $matches[1] ?? (isset($matches[0]) ? $this->searchValue(option: $option, arg: $matches[0]) : null);

                if ($option->type === OptionType::REQUIRED && $value === null) {
                    $name = mb_trim($option->long, '--');

                    return $this->tryThrow(message: "Не передана обязательная опция $name");
                }

                $args[mb_trim($option->long, '--')] = $value;
            }
        }

        return $args;
    }

    /**
     * Получить список команд
     *
     * @param array<array-key, class-string<Command>> $commands
     */
    public function echoList(array $commands): void
    {
        if ($commands === []) {
            return;
        }

        foreach ($commands as $name => $command) {
            $signature = $name . ' ';

            $options = $command::getOptions();

            foreach ($options as $option) {
                $signature . $option->long . '|' . $option->short . ' ';
            }

            echo PHP_EOL;
            echo $signature . PHP_EOL;
            echo $command::getDescription() . PHP_EOL;
            echo PHP_EOL;
        }
    }

    /**
     * Получить справку по командe
     *
     * @param class-string<Command> $command
     */
    public function echoHelp(string $name, string $command): void
    {
        $options = $command::getOptions();

        echo $name . ' ' . PHP_EOL;

        foreach ($options as $option) {
            echo $option->long . ', ' . $option->short . ' ' . $option->description . PHP_EOL;
        }

        echo $command::getDescription() . PHP_EOL;
    }

    /**
     * Найти значение опции в аргументе
     *
     * @return bool[]|string|bool|null
     */
    private function searchValue(Option $option, string $arg): array|string|bool|null
    {
        if ($option->type === OptionType::FLAG && str_starts_with($arg, '-') && !str_starts_with($arg, '--')) {
            $count = mb_substr_count($arg, mb_trim($option->short, '-'));

            return match (true) {
                $count === 0 => null,
                $count === 1 => true,
                $count > 0   => array_fill(0, $count, true),
            };
        }

        if ($option->long !== '' && str_starts_with($arg, '--')) {
            $matches = explode($option->long, $arg);
        } elseif ($option->short !== '' && str_starts_with($arg, '-')) {
            $matches = explode($option->short, $arg);
        } else {
            return null;
        }

        return isset($matches[1])
            ? ($matches[1] !== ' ' ? $matches[1] : null)
            : null;
    }

    /**
     * Выбросить исключение в try-catch
     *
     * @return array{message: string}
     */
    private function tryThrow(string $message): array
    {
        try {
            throw new \Exception(message: $message);
        } catch (\Throwable $e) {
            return [
                'message' => $e->getMessage(),
            ];
        }
    }
}
