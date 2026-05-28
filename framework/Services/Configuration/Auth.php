<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Configuration;

use Simpledynamic\Providers\Facade;

/**
 * Фасад конфигурации авторизации
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
