<?php

declare(strict_types=1);

namespace Sympledynamic\Providers;

use Sympledynamic\Container\ServiceContainer;

/**
 * Базовый сервис-провайдер
 *
 * @api
 */
abstract class ServiceProvider
{
    /**
     * @param ServiceContainer $app
     */
    public function __construct(
        protected ServiceContainer $app,
    ) {
    }

    /**
     * Зарегистрировать биндинги
     *
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function register(): void
    {
    }

    /**
     * Выполнить действия после регистрации биндингов
     *
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function boot(): void
    {
    }
}
