<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Configuration;

use Simpledynamic\Providers\Facade;

/**
 * Фасад онфигурация ORM
 */
final class ORM extends Facade
{
    /**
     * Получить акксесор фасада
     *
     * @psalm-suppress PossiblyUnusedMethod
     */
    #[\Override]
    protected static function getFacadeAccessor(): string
    {
        return ORM::class;
    }
}
