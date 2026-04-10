<?php

declare(strict_types=1);

namespace Sympledynamic\Container;

use config\App;

/**
 * Компонент для управления процессом регистрации и инициализации сервис-провайдеров
 *
 * @psalm-suppress ClassCanBeFinal
 */
class ProviderManager
{
    private array $providers = [];

    /**
     * Построить сервис-контейнер
     */
    public function buildContainer(): ServiceContainer
    {
        $container = new ServiceContainer();
        $providers = App::getConfigPart('providers') ?? [];

        if ($providers === []) {
            return $container;
        }

        foreach ($providers as $providerClass) {
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
