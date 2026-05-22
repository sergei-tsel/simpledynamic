<?php

declare(strict_types=1);

namespace Simpledynamic\Services\CLI;

use Simpledynamic\Container\ProviderManager;

/**
 * Базовая консольная команда
 *
 * @api
 * @psalm-suppress ClassCanBeFinal
 */
class Command
{
    protected string $signature = '';

    /**
     * @psalm-suppress PossiblyUnusedProperty
     */
    protected string $description = '';

    /** @var array<array-key, array<int, bool>|string|bool> */
    protected array $arguments = [];

    /**
     * Выполнить команду
     */
    public static function run(): void
    {
        $container = new ProviderManager()->buildContainer();

        /** @var Command $command */
        $command = $container->resolve(static::class);

        if (!method_exists($command, 'handle')) {
            return;
        }

        $command->parseCliArguments();

        $dependencies = $container->resolveMethodDependencies(static::class, 'handle');

        /** @psalm-suppress UndefinedMethod */
        $command->handle(...array_values($dependencies));
    }

    /**
     * Распарсить аргументы командной строки
     */
    protected function parseCliArguments(): void
    {
        if ($this->signature === '') {
            return;
        }

        preg_match_all('/(-\w+)|(--\w+)/', $this->signature, $matches);

        $shortOpts = '';
        $longOpts = [];
        $optsWithoutValues = [];

        foreach ($matches[0] as $match) {
            $opt = trim($match, "{}");

            if (str_starts_with($opt, '--')) {
                $longOpts[] = substr($opt, 2);
            } elseif (str_starts_with($opt, '-')) {
                $shortOpts .= substr($opt, 1);
            }

            if (!str_ends_with($opt, ':')) {
                $optsWithoutValues[] = $opt;
            }
        }

        /** @var array<array-key, array<int, bool>|string|bool> $parsedOptions */
        $parsedOptions = getopt($shortOpts, $longOpts);

        foreach ($parsedOptions as $key => $value) {
            $hasNoValue = in_array($key, $optsWithoutValues);

            if ($hasNoValue && is_array($value)) {
                $this->arguments[$key] = array_fill(0, count($value), true);
            } elseif ($hasNoValue) {
                $this->arguments[$key] = isset($this->arguments[$key]);
            } else {
                $this->arguments[$key] = $value;
            }
        }
    }

    /**
     * Получить аргумент
     *
     * @psalm-suppress PossiblyUnusedMethod
     */
    protected function getArgument(string $name): array|string|bool|null
    {
        return $this->arguments[$name] ?? null;
    }
}
