<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Filtration\Validation;

/**
 * Флаг для фильтра FILTER_VALIDATE_EMAIL
 */
enum EmailFilterFlag: int
{
    private const int FILTER_FLAG_EMAIL_UNICODE = FILTER_FLAG_EMAIL_UNICODE;

    case EMAIL_UNICODE = self::FILTER_FLAG_EMAIL_UNICODE;
}
