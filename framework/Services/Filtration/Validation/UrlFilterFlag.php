<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Filtration\Validation;

/**
 * Флаг для фильтра FILTER_VALIDATE_URL
 */
enum UrlFilterFlag: int
{
    private const int FILTER_FLAG_PATH_REQUIRED = FILTER_FLAG_PATH_REQUIRED;
    private const int FILTER_FLAG_QUERY_REQUIRED = FILTER_FLAG_QUERY_REQUIRED;

    case PATH_REQUIRED = self::FILTER_FLAG_PATH_REQUIRED;
    case QUERY_REQUIRED = self::FILTER_FLAG_QUERY_REQUIRED;
}
