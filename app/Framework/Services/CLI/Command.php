<?php

declare(strict_types=1);

namespace App\Framework\Services\CLI;

use App\Infrastructure\Container\ProviderManager;

/**
 * Базовая консольная команда
 */
class Command
{
    protected string $signature = '';
    protected string $description = '';
    protected array $arguments = [];

    /**
     * Выполнить команду
     */
    public static function run(): void
    {
        $container = new ProviderManager()->buildContainer();

        /** @var Command $command */
        $command = $container->resolve(static::class);
        $command->parseCliArguments();

        $dependencies = $container->resolveMethodDependencies(static::class, 'handle');

        $command->handle(...$dependencies);
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

            if(!str_ends_with($opt, ':')) {
                $optsWithoutValues[] = $opt;
            }
        }

        $parsedOptions = getopt($shortOpts, $longOpts);

        foreach ($parsedOptions as $key => $value) {
            $hasNoValue = in_array($key, $optsWithoutValues);

            if ($hasNoValue && is_array($value)) {
                foreach ($value as $v) {
                    $this->arguments[$key][] = true;
                }
            } elseif ($hasNoValue) {
                $this->arguments[$key] = isset($this->arguments[$key]);
            } else {
                $this->arguments[$key] = $value;
            }
        }
    }

    /**
     * Получить аргумент
     */
    protected function getArgument(string $name): string|bool|null
    {
        return $this->arguments[$name] ?? null;
    }
}
