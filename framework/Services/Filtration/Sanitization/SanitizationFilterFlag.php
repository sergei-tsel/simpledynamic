<?php

declare(strict_types=1);

namespace Sympledynamic\Services\Filtration\Sanitization;

/**
 * Флаг, совместимый с фильтрами для санитизации данных
 */
enum SanitizationFilterFlag: int
{
    private const int FILTER_FLAG_STRIP_LOW = FILTER_FLAG_STRIP_LOW;
    private const int FILTER_FLAG_STRIP_HIGH = FILTER_FLAG_STRIP_HIGH;
    private const int FILTER_FLAG_STRIP_BACKTICK = FILTER_FLAG_STRIP_BACKTICK;
    private const int FILTER_FLAG_ENCODE_LOW = FILTER_FLAG_ENCODE_LOW;
    private const int FILTER_FLAG_ENCODE_HIGH = FILTER_FLAG_ENCODE_HIGH;
    private const int FILTER_FLAG_ENCODE_AMP = FILTER_FLAG_ENCODE_AMP;
    private const int FILTER_FLAG_NO_ENCODE_QUOTES = FILTER_FLAG_NO_ENCODE_QUOTES;
    private const int FILTER_FLAG_EMPTY_STRING_NULL = FILTER_FLAG_EMPTY_STRING_NULL;

    case STRIP_LOW = self::FILTER_FLAG_STRIP_LOW;
    case STRIP_HIGH = self::FILTER_FLAG_STRIP_HIGH;
    case STRIP_BACKTICK = self::FILTER_FLAG_STRIP_BACKTICK;
    case ENCODE_LOW = self::FILTER_FLAG_ENCODE_LOW;
    case ENCODE_HIGH = self::FILTER_FLAG_ENCODE_HIGH;
    case ENCODE_AMP = self::FILTER_FLAG_ENCODE_AMP;
    case NO_ENCODE_QUOTES = self::FILTER_FLAG_NO_ENCODE_QUOTES;
    case EMPTY_STRING_NULL = self::FILTER_FLAG_EMPTY_STRING_NULL;
}
