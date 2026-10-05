<?php

declare(strict_types=1);

namespace Test\public;

use Simpledynamic\Container\ServiceContainer;
use Simpledynamic\Services\CLI\Command;
use Simpledynamic\Services\CLI\CommandLineManager;
use Simpledynamic\Services\Configuration\Env;
use Simpledynamic\Services\Configuration\Headers;
use Simpledynamic\Services\Logging\Logger;
use Simpledynamic\Services\Routing\InputResolver;
use Simpledynamic\Services\Routing\MiddlewaresResolver;
use Simpledynamic\Services\Routing\RouteBuilder;
use Simpledynamic\Services\Routing\Router;
use Simpledynamic\Services\Routing\UriBuilder;
use Test\Config\App;

/**
 * Единая точка входа приложения
 *
 * В веб-режиме обрабатывает HTTP-запрос через роутер, в CLI-режиме выполняет команду.
 */
final class EntryPoint
{
    /**
     * Запустить приложение
     *
     * Точка входа — внешняя граница приложения, объявлять @throws здесь некуда:
     * вызывающего кода нет. Поэтому исключения перехватываются и записываются в лог,
     * а в CLI дополнительно выводятся в stderr с ненулевым кодом возврата.
     */
    public static function run(): void
    {
        if (PHP_SAPI === 'cli') {
            self::runCli();

            return;
        }

        self::runWeb();
    }

    /**
     * Выполнить команду, переданную в аргументах командной строки
     */
    private static function runCli(): void
    {
        self::bootLogger();

        try {
            $exitCode = self::dispatchCliCommand();
        } catch (\Throwable $exception) {
            Logger::report($exception);
            fwrite(STDERR, $exception->getMessage() . PHP_EOL);

            exit(1);
        }

        exit($exitCode);
    }

    /**
     * Разобрать аргументы командной строки и выполнить команду
     *
     * @return int Код возврата для exit
     * @throws \Exception
     * @throws \ReflectionException
     */
    private static function dispatchCliCommand(): int
    {
        $config = new App();

        // Контейнер инициализируется конфигурацией до первого обращения к фасадам
        ServiceContainer::getInstance($config);

        /**
         * @var array<array-key, class-string<Command>> $commands
         */
        $commands = App::getConfigPart('commands') ?? [];

        $name = $_SERVER['argv'][1] ?? null;

        if (!is_string($name) || $name === '' || $name === 'list') {
            new CommandLineManager()->echoList(commands: $commands);

            return 0;
        }

        if (!array_key_exists($name, $commands)) {
            return 1;
        }

        $command = $commands[$name];

        // Проверка class_exists оставлена как защита от ошибки автозагрузки:
        // имя класса из конфига может не разрешиться, даже если объявлено строкой
        if (!class_exists($command)) {
            return 1;
        }

        if (self::isHelpRequested()) {
            new CommandLineManager()->echoHelp(name: $name, command: $command);

            return 0;
        }

        $command::run($config);

        return 0;
    }

    /**
     * Проверить, запрошен ли вызов справки по команде
     *
     * @param list<string> $helpArgs Названия аргументов вызова справки
     */
    private static function isHelpRequested(array $helpArgs = ['--help', '-h', 'help']): bool
    {
        return array_intersect($helpArgs, array_slice($_SERVER['argv'] ?? [], 2)) !== [];
    }

    /**
     * Обработать HTTP-запрос
     */
    private static function runWeb(): void
    {
        self::bootLogger();

        try {
            self::handleRequest();
        } catch (\Throwable $exception) {
            Logger::report($exception);
        }
    }

    /**
     * Инициализировать логгер и перенаправить лог PHP в каталог логов
     */
    private static function bootLogger(): void
    {
        ini_set('log_errors', '1');
        ini_set('error_log', __DIR__ . '/../logs/php_errors_' . date('Y-m-d') . '.log');

        new Logger(__DIR__ . '/../logs/');
    }

    /**
     * Обработать HTTP-запрос роутером
     *
     * @throws \Exception
     * @throws \ReflectionException
     */
    private static function handleRequest(): void
    {
        $config = new App();

        // Контейнер инициализируется конфигурацией до первого обращения к фасадам
        ServiceContainer::getInstance($config);

        $uriBuilder = new UriBuilder();
        $filePath = __DIR__ . $uriBuilder->buildRequestUri()->getPath();

        if (is_file($filePath)) {
            self::serveStaticFile(path: $filePath);

            return;
        }

        Env::set();

        $inputResolver = new InputResolver();

        $router = new Router(
            uriBuilder: $uriBuilder,
            routeBuilder: new RouteBuilder(),
            inputResolver: $inputResolver,
            middlewaresResolver: new MiddlewaresResolver(inputResolver: $inputResolver),
        );

        $router->setConfig($config);
        $router->handle();

        Headers::set();
    }

    /**
     * Отдать статический файл
     */
    private static function serveStaticFile(string $path): void
    {
        $contentType = mime_content_type($path);

        if (is_string($contentType)) {
            header('Content-type: ' . $contentType . '; charset=utf-8');
        }

        if (str_ends_with(strtolower($path), '.php')) {
            include $path;

            return;
        }

        readfile($path);
    }
}
