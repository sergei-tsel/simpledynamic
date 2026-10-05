<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Configuration;

use Simpledynamic\Providers\Facade;

/**
 * Фасад онфигурация ORM
 *
 * @method static array getMigrationDirectories()
 * @method static array<string, array<array-key, mixed>|scalar|null> getConfig()
 */
final class ORM extends Facade
{
    /**
     * Получить акксесор фасада
     */
    #[\Override]
    protected static function getFacadeAccessor(): string
    {
        return ORM::class;
    }
}
