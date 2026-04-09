<?php

declare(strict_types=1);

namespace Sympledynamic\Providers;

use Sympledynamic\Container\ServiceContainer;

/**
 * Базовый сервис-провайдер
 */
abstract class ServiceProvider
{
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function __construct(
        /**
         * @var ServiceContainer
         */
        protected $app,
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
