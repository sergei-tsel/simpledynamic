<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Filtration\Validation;

/**
 * Опция для фильтра FILTER_VALIDATE_REGEXP
 */
enum RegexpFilterOption: string
{
    case REGEXP = 'regexp';
}
