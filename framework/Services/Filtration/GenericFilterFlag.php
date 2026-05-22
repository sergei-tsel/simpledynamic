<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Filtration;

/**
 * Общий флаг, применимый ко всем фильтрам
 */
enum GenericFilterFlag: int
{
    private const int FILTER_FLAG_NONE = FILTER_FLAG_NONE;
    private const int FILTER_REQUIRE_SCALAR = FILTER_REQUIRE_SCALAR;
    private const int FILTER_REQUIRE_ARRAY = FILTER_REQUIRE_ARRAY;
    private const int FILTER_FORCE_ARRAY = FILTER_FORCE_ARRAY;
    private const int FILTER_NULL_ON_FAILURE = FILTER_NULL_ON_FAILURE;

    case FLAG_NONE = self::FILTER_FLAG_NONE;
    case REQUIRE_SCALAR = self::FILTER_REQUIRE_SCALAR;
    case REQUIRE_ARRAY = self::FILTER_REQUIRE_ARRAY;
    case FORCE_ARRAY = self::FILTER_FORCE_ARRAY;
    case NULL_ON_FAILURE = self::FILTER_NULL_ON_FAILURE;
}
