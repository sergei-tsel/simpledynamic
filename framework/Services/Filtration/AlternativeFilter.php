<?php

declare(strict_types=1);

namespace Sympledynamic\Services\Filtration;

/**
 * Альтернативный фильтр
 */
enum AlternativeFilter: int
{
    private const int FILTER_CALLBACK = FILTER_CALLBACK;
    case CALLBACK = self::FILTER_CALLBACK;
}
