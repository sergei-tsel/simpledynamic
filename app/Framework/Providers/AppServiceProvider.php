<?php

declare(strict_types=1);

namespace App\Framework\Providers;

use config\ODM;
use config\ORM;
use Doctrine\ODM\MongoDB\DocumentManager;
use Illuminate\Database\DatabaseManager;

/**
 * Сервис-провайдер приложения
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Зарегистрировать биндинги
     */
    #[\Override]
    public function register(): void
    {
        $this->app->singleton(DatabaseManager::class, fn($app): \Illuminate\Database\DatabaseManager => ORM::createEloquent());

        $this->app->singleton(DocumentManager::class, fn($app): \Doctrine\ODM\MongoDB\DocumentManager => ODM::createDoctrineMongoDB());
    }

    /**
     * Выполнить действия после регистрации биндингов
     */
    #[\Override]
    public function boot(): void
    {
    }
}
