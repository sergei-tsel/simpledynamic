<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Configuration;

use Simpledynamic\Providers\Facade;

/**
 * Фасад конфигурации авторизации
 *
 * @method static mixed hash(string $argName, string $argValue)
 * @method static mixed getConfigPart(string $argName)
 */
final class Auth extends Facade
{
    /**
     * Получить акксесор фасада
     */
    #[\Override]
    protected static function getFacadeAccessor(): string
    {
        return Auth::class;
    }
}
