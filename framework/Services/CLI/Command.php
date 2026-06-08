<?php

declare(strict_types=1);

namespace Simpledynamic\Services\CLI;

use Simpledynamic\Container\ServiceContainer;
use Simpledynamic\Services\Reflection\ClassReflectionManager;

/**
 * Базовая консольная команда
 *
 * @api
 * @psalm-suppress ClassCanBeFinal
 */
class Command
{
    /**
     * @psalm-suppress PossiblyUnusedProperty
     */
    protected static string $description = '';

    /** @var array<array-key, array<array-key, bool>|string|bool|null> */
    protected array $arguments = [];

    /**
     * Выполнить команду
     */
    public static function run(): void
    {
        $container = ServiceContainer::getInstance();

        /** @var Command $command */
        $command = $container->resolve(static::class);

        if (!method_exists($command, 'handle')) {
            return;
        }

        $dependencies = $container->resolveMethodDependencies(static::class, 'handle');

        $args = new CommandLineManager()->getArgs(options: static::getOptions());

        if (array_key_exists('message', $args) && is_string($args['message'])) {
            echo $args['message'];
            exit(1);
        }

        $command->arguments = $args;

        /** @psalm-suppress UndefinedMethod */
        $command->handle(...array_values($dependencies));
    }

    /**
     * Получить ожидаемые опции
     *
     * @return Option[]
     */
    public static function getOptions(): array
    {
        /** @var array<string, array<class-string<Option>, list<Option>>> $commandAttributes */
        $commandAttributes = new ClassReflectionManager()->readAttributes(class: static::class, attributesNames: [
            'class'  => [
                Option::class,
            ],
        ]);

        return $commandAttributes['class'][Option::class];
    }

    /**
     * Получить описание
     */
    public static function getDescription(): string
    {
        return static::$description;
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
