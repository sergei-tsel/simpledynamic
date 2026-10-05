<?php

declare(strict_types=1);

namespace Test\Framework\Providers;

use Doctrine\ODM\MongoDB\DocumentManager;
use Illuminate\Database\DatabaseManager;
use Simpledynamic\Providers\ServiceProvider;
use Simpledynamic\Services\Configuration\App as AppFacade;
use Simpledynamic\Services\Configuration\Auth as AuthFacade;
use Simpledynamic\Services\Configuration\Cookies as CookiesFacade;
use Simpledynamic\Services\Configuration\Env as EnvFacade;
use Simpledynamic\Services\Configuration\Headers as HeadersFacade;
use Simpledynamic\Services\Configuration\ODM as ODMFacade;
use Simpledynamic\Services\Configuration\ORM as ORMFacade;
use Simpledynamic\Services\Configuration\Routes as RoutesFacade;
use Simpledynamic\Services\Configuration\Session as SessionFacade;
use Test\Config\App as AppConfig;
use Test\Config\Auth as AuthConfig;
use Test\Config\Cookies as CookiesConfig;
use Test\Config\Env as EnvConfig;
use Test\Config\Headers as HeadersConfig;
use Test\Config\ODM as ODMConfig;
use Test\Config\ORM as ORMConfig;
use Test\Config\Routes as RoutesConfig;
use Test\Config\Session as SessionConfig;

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
        $this->app->singleton(AppFacade::class, AppConfig::class);
        $this->app->singleton(AuthFacade::class, AuthConfig::class);
        $this->app->singleton(CookiesFacade::class, CookiesConfig::class);
        $this->app->singleton(EnvFacade::class, EnvConfig::class);
        $this->app->singleton(HeadersFacade::class, HeadersConfig::class);
        $this->app->singleton(ODMFacade::class, ODMConfig::class);
        $this->app->singleton(ORMFacade::class, ORMConfig::class);
        $this->app->singleton(RoutesFacade::class, RoutesConfig::class);
        $this->app->singleton(SessionFacade::class, SessionConfig::class);

        $this->app->singleton(DatabaseManager::class, static fn(): DatabaseManager => ORMConfig::createEloquent()->getDatabaseManager());
        $this->app->singleton(DocumentManager::class, ODMConfig::createDoctrineMongoDB(...));
    }

    /**
     * Выполнить действия после регистрации биндингов
     */
    #[\Override]
    public function boot(): void {}
}
