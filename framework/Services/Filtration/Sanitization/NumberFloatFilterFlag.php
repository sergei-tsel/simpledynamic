<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Filtration\Sanitization;

/**
 * Флаг для фильтра FILTER_SANITIZE_NUMBER_FLOAT
 */
enum NumberFloatFilterFlag: int
{
    private const int FILTER_FLAG_ALLOW_FRACTION = FILTER_FLAG_ALLOW_FRACTION;
    private const int FILTER_FLAG_ALLOW_THOUSAND = FILTER_FLAG_ALLOW_THOUSAND;
    private const int FILTER_FLAG_ALLOW_SCIENTIFIC = FILTER_FLAG_ALLOW_SCIENTIFIC;

    case ALLOW_FRACTION = self::FILTER_FLAG_ALLOW_FRACTION;
    case ALLOW_THOUSAND = self::FILTER_FLAG_ALLOW_THOUSAND;
    case ALLOW_SCIENTIFIC = self::FILTER_FLAG_ALLOW_SCIENTIFIC;
}
