<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Filtration\Validation;

/**
 * Флаг для фильтра FILTER_VALIDATE_DOMAIN
 */
enum DomainFilterFlag: int
{
    private const int FILTER_FLAG_HOSTNAME = FILTER_FLAG_HOSTNAME;

    case HOSTNAME = self::FILTER_FLAG_HOSTNAME;
}
