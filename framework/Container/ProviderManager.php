<?php

declare(strict_types=1);

namespace Simpledynamic\Container;

use config\App;
use Simpledynamic\Providers\ServiceProvider;

/**
 * Компонент для управления процессом регистрации и инициализации сервис-провайдеров
 *
 * @api
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

        if (!is_array($providers)) {
            return $container;
        }

        foreach ($providers as $providerClass) {
            if (!is_string($providerClass) || !class_exists($providerClass)) {
                continue;
            }

            /**
             * @var class-string<ServiceProvider> $providerClass
             * @psalm-suppress UnsafeInstantiation
             */
            $providerInstance = new $providerClass($container);
            $this->providers[] = $providerInstance;
            $providerInstance->register();
        }

        /** @var ServiceProvider $provider */
        foreach ($this->providers as $provider) {
            $provider->boot();
        }

        return $container;
    }
}
