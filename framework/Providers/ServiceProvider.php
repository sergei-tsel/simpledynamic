<?php

declare(strict_types=1);

namespace Framework\Providers;

use Framework\Container\ServiceContainer;

/**
 * Базовый сервис-провайдер
 */
abstract class ServiceProvider
{
    public function __construct(
        /**
         * @var ServiceContainer
         */
        protected $app,
    ) {
    }

    /**
     * Зарегистрировать биндинги
     */
    public function register(): void
    {
    }

    /**
     * Выполнить действия после регистрации биндингов
     */
    public function boot(): void
    {
    }
}
