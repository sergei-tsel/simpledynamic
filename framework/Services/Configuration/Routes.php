<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Configuration;

use Simpledynamic\Providers\Facade;

/**
 * Фасад конфигурации роутов
 */
final class Routes extends Facade
{
    /**
     * Получить акксесор фасада
     */
    #[\Override]
    protected static function getFacadeAccessor(): string
    {
        return Routes::class;
    }
}
