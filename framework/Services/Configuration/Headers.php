<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Configuration;

use Simpledynamic\Providers\Facade;

/**
 * Фасад конфигурации заголовков
 *
 * @method static mixed set()
 * @method static mixed getConfigPart(string $argName)
 */
final class Headers extends Facade
{
    /**
     * Получить акксесор фасада
     */
    #[\Override]
    protected static function getFacadeAccessor(): string
    {
        return Headers::class;
    }
}
