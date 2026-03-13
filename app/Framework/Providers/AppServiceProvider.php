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
    public function register(): void
    {
        $this->app->singleton(DatabaseManager::class, function ($app) {
            return ORM::createEloquent();
        });

        $this->app->singleton(DocumentManager::class, function ($app) {
            return ODM::createDoctrineMongoDB();
        });
    }

    /**
     * Выполнить действия после регистрации биндингов
     */
    public function boot(): void
    {
    }
}
