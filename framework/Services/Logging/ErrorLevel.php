<?php

declare(strict_types=1);

namespace Framework\Services\Logging;

/**
 * Уровень ошибки
 */
enum ErrorLevel: int
{
    case EXCEPTION           = 0;
    case E_ERROR             = 1;
    case E_WARNING           = 2;
    case E_PARSE             = 4;
    case E_NOTICE            = 8;
    case E_CORE_ERROR        = 16;
    case E_CORE_WARNING      = 32;
    case E_COMPILE_ERROR     = 64;
    case E_COMPILE_WARNING   = 128;
    case E_USER_ERROR        = 256;
    case E_USER_WARNING      = 512;
    case E_USER_NOTICE       = 1024;
    case E_STRICT            = 2048;
    case E_RECOVERABLE_ERROR = 4096;
    case E_DEPRECATED        = 8192;
    case E_USER_DEPRECATED   = 16384;
    case UNKNOWN             = 32768;
}
