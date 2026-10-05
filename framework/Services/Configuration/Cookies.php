<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Configuration;

use Simpledynamic\Providers\Facade;

/**
 * Фасад конфигурации куки
 *
 * @method static mixed set(string $sessionId)
 * @method static mixed getConfigPart(string $argName)
 */
final class Cookies extends Facade
{
    /**
     * Получить акксесор фасада
     */
    #[\Override]
    protected static function getFacadeAccessor(): string
    {
        return Cookies::class;
    }
}
