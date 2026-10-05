<?php

declare(strict_types=1);

namespace Simpledynamic\Container;

use Simpledynamic\Providers\ServiceProvider;
use Simpledynamic\Services\Configuration\Config;

/**
 * Компонент для управления процессом регистрации и инициализации сервис-провайдеров
 */
class ProviderBootsrapper
{
    /** @var list<ServiceProvider> */
    private array $providers = [];

    /**
     * Построить сервис-контейнер
     */
    public function buildContainer(?Config $config = null): ServiceContainer
    {
        $container = new ServiceContainer();

        $providers = $config === null ? [] : $config::getConfigPart('providers') ?? [];

        if (!is_array($providers)) {
            return $container;
        }

        /** @var list<class-string<ServiceProvider>> $providers */
        $providers = array_filter($providers, static fn(mixed $v): bool => is_string($v) && class_exists($v));

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
