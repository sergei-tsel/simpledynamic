<?php

declare(strict_types=1);

namespace Framework\Services\Routing;

/**
 * Типы входных данных
 */
enum InputTypes: int
{
    private const int INPUT_POST = INPUT_POST;
    private const int INPUT_GET = INPUT_GET;
    private const int INPUT_COOKIE = INPUT_COOKIE;
    private const int INPUT_ENV = INPUT_ENV;
    private const int INPUT_SERVER = INPUT_SERVER;

    case POST = self::INPUT_POST;
    case GET = self::INPUT_GET;
    case COOKIE = self::INPUT_COOKIE;
    case FILES = 3;
    case ENV = self::INPUT_ENV;
    case SERVER = self::INPUT_SERVER;
    case SESSION = 6;
}
