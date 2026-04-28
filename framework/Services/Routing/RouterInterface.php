<?php

declare(strict_types=1);

namespace Sympledynamic\Services\Routing;

/**
 * Интерфейс класса, содержащего список роутов
 */
interface RouterInterface
{
    public static function getRoutes(): array;
}
