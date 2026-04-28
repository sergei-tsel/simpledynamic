<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Filtration\Validation;

/**
 * Флаг для фильтра FILTER_VALIDATE_FLOAT
 */
enum FloatFilterFlag: int
{
    private const int FILTER_FLAG_ALLOW_THOUSAND = FILTER_FLAG_ALLOW_THOUSAND;

    case ALLOW_THOUSAND = self::FILTER_FLAG_ALLOW_THOUSAND;
}
