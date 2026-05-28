<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Configuration;

use Simpledynamic\Providers\Facade;

/**
 * Фасад конфигурации ODM
 */
final class ODM extends Facade
{
    /**
     * Получить акксесор фасада
     */
    #[\Override]
    protected static function getFacadeAccessor(): string
    {
        return ODM::class;
    }
}
