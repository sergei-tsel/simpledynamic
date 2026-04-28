<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Filtration\Validation;

/**
 * Флаг для фильтра FILTER_VALIDATE_IP
 */
enum IpFilterFlag: int
{
    private const int FILTER_FLAG_IPV4 = FILTER_FLAG_IPV4;
    private const int FILTER_FLAG_IPV6 = FILTER_FLAG_IPV6;
    private const int FILTER_FLAG_NO_RES_RANGE = FILTER_FLAG_NO_RES_RANGE;
    private const int FILTER_FLAG_NO_PRIV_RANGE = FILTER_FLAG_NO_PRIV_RANGE;
    private const int FILTER_FLAG_GLOBAL_RANGE = FILTER_FLAG_GLOBAL_RANGE;

    case IPV4 = self::FILTER_FLAG_IPV4;
    case IPV6 = self::FILTER_FLAG_IPV6;
    case NO_RES_RANGE = self::FILTER_FLAG_NO_RES_RANGE;
    case NO_PRIV_RANGE = self::FILTER_FLAG_NO_PRIV_RANGE;
    case GLOBAL_RANGE = self::FILTER_FLAG_GLOBAL_RANGE;
}
