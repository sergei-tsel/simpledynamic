<?php

declare(strict_types=1);

namespace App\Framework\Providers;

use App\Infrastructure\Container\Container;

/**
 * Базовый сервис-провайдер
 */
abstract class ServiceProvider
{
    public function __construct(
        /**
         * @var Container
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
