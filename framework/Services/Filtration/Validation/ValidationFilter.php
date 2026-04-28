<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Filtration\Validation;

/**
 * Фильтр для валидации данных
 */
enum ValidationFilter: int
{
    private const int FILTER_VALIDATE_BOOL = FILTER_VALIDATE_BOOL;
    private const int FILTER_VALIDATE_INT = FILTER_VALIDATE_INT;
    private const int FILTER_VALIDATE_FLOAT = FILTER_VALIDATE_FLOAT;
    private const int FILTER_VALIDATE_REGEXP = FILTER_VALIDATE_REGEXP;
    private const int FILTER_VALIDATE_URL = FILTER_VALIDATE_URL;
    private const int FILTER_VALIDATE_DOMAIN = FILTER_VALIDATE_DOMAIN;
    private const int FILTER_VALIDATE_EMAIL = FILTER_VALIDATE_EMAIL;
    private const int FILTER_VALIDATE_IP = FILTER_VALIDATE_IP;
    private const int FILTER_VALIDATE_MAC = FILTER_VALIDATE_MAC;

    case BOOL = self::FILTER_VALIDATE_BOOL;
    case INT = self::FILTER_VALIDATE_INT;
    case FLOAT = self::FILTER_VALIDATE_FLOAT;
    case REGEXP = self::FILTER_VALIDATE_REGEXP;
    case URL = self::FILTER_VALIDATE_URL;
    case DOMAIN = self::FILTER_VALIDATE_DOMAIN;
    case EMAIL = self::FILTER_VALIDATE_EMAIL;
    case IP = self::FILTER_VALIDATE_IP;
    case MAC = self::FILTER_VALIDATE_MAC;
}
