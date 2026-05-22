<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Routing;

/**
 * Глобальный массив, для которого нет InputType
 */
enum GlobalArray: string
{
    case FILES = 'files';
    case SESSION = 'session';
}
