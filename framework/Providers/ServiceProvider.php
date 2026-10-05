<?php

declare(strict_types=1);

namespace Simpledynamic\Providers;

use Simpledynamic\Container\ServiceContainer;

/**
 * Базовый сервис-провайдер
 *
 * @consistent-constructor
 */
abstract class ServiceProvider
{
    /**
     * @param ServiceContainer $app
     */
    public function __construct(
        protected ServiceContainer $app,
    ) {}

    /**
     * Зарегистрировать биндинги
     */
    public function register(): void {}

    /**
     * Выполнить действия после регистрации биндингов
     */
    public function boot(): void {}
}
