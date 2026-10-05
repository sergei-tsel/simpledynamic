<?php

declare(strict_types=1);

namespace Simpledynamic\Services\CLI;

use ReflectionClass;
use Simpledynamic\Container\ServiceContainer;
use Simpledynamic\Services\Configuration\Config;
use Simpledynamic\Services\Reflection\ClassReflectionBuilder;

/**
 * Базовая консольная команда
 */
class Command
{
    protected static string $description = '';

    /** @var array<array-key, array<array-key, bool>|string|bool|null> */
    protected array $arguments = [];

    /**
     * Выполнить команду
     *
     * @throws \Exception
     * @throws \ReflectionException
     */
    public static function run(?Config $config = null): void
    {
        $container = ServiceContainer::getInstance($config);

        /** @var Command $command */
        $command = $container->resolve(static::class);

        if (!method_exists($command, 'handle')) {
            return;
        }

        $args = new CommandLineManager()->getArgs(options: static::getOptions());

        if (array_key_exists('message', $args) && is_string($args['message'])) {
            echo $args['message'] . PHP_EOL;
            exit(1);
        }

        $command->arguments = $args;

        $container->executeOn(object: $command, className: static::class, methodName: 'handle');
    }

    /**
     * Получить ожидаемые опции
     *
     * @return list<Option>
     * @throws \ReflectionException
     */
    public static function getOptions(): array
    {
        $classBuilder = new ClassReflectionBuilder(reflectionClass: new ReflectionClass(static::class));

        /** @var array<string, array<class-string<Option>, list<Option>>> $commandAttributes */
        $commandAttributes = $classBuilder->readAttributes(attributesNames: [
            'class' => [
                Option::class,
            ],
        ]);

        if (
            !array_key_exists('class', $commandAttributes)
            || !array_key_exists(Option::class, $commandAttributes['class'])
        ) {
            return [];
        }

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
     * @return array<array-key, bool>|string|bool|null
     */
    protected function getArgument(string $name): array|string|bool|null
    {
        return $this->arguments[$name] ?? null;
    }
}
