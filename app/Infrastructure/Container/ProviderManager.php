<?php

declare(strict_types=1);

namespace App\Infrastructure\Container;

use config\Providers;

/**
 * Компонент для управления процессом регистрации и инициализации сервис-провайдеров
 */
class ProviderManager
{
    private array $providers = [];

    /**
     * Построить сервис-контейнер
     */
    public function buildContainer(): Container
    {
        $container = new Container();

        foreach (Providers::getConfig() as $providerClass) {
            $providerInstance = new $providerClass($container);
            $this->providers[] = $providerInstance;
            $providerInstance->register();
        }

        foreach ($this->providers as $provider) {
            $provider->boot();
        }

        return $container;
    }
}
