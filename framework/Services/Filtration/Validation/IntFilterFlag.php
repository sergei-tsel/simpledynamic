<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Filtration\Validation;

/**
 * Флаг для фильтра FILTER_VALIDATE_INT
 */
enum IntFilterFlag: int
{
    private const int FILTER_FLAG_ALLOW_OCTAL = FILTER_FLAG_ALLOW_OCTAL;
    private const int FILTER_FLAG_ALLOW_HEX = FILTER_FLAG_ALLOW_HEX;

    case ALLOW_OCTAL = self::FILTER_FLAG_ALLOW_OCTAL;
    case ALLOW_HEX = self::FILTER_FLAG_ALLOW_HEX;
}
