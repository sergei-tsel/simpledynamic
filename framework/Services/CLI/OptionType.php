<?php

declare(strict_types=1);

namespace Simpledynamic\Services\CLI;

/**
 * Тип опции консольной команды
 */
enum OptionType: int
{
    case FLAG = -1;
    case OPTIONAL = 0;
    case REQUIRED = 1;
}
