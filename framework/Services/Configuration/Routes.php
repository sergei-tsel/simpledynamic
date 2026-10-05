<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Configuration;

use Simpledynamic\Providers\Facade;

/**
 * Фасад конфигурации роутов
 *
 * @method static string getBase()
 * @method static string|null getGroupKey(string $path)
 * @method static class-string|null getGroup(string $path)
 * @method static string getDefaultGroupPath()
 * @method static mixed getRoute()
 * @method static mixed getConfigPart(string $argName)
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
