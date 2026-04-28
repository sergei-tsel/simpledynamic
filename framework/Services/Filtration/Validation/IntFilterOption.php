<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Filtration\Validation;

/**
 * Опция для фильтра FILTER_VALIDATE_INT
 */
enum IntFilterOption: string
{
    case MIN_RANGE = 'min_range';
    case MAX_RANGE = 'max_range';
}
