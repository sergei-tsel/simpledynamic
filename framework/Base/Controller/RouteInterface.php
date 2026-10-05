<?php

declare(strict_types=1);

namespace Simpledynamic\Base\Controller;

use Closure;
use Simpledynamic\Services\Configuration\Config;

/**
 * Роут
 */
interface RouteInterface
{
    /**
     * Получить экшен
     */
    public function getAction(): Closure|string;

    /**
     * Получить HTTP-метод
     */
    public function getMethod(): string;

    /**
     * Получить путь
     */
    public function getPath(): string;

    /**
     * Получить имя
     */
    public function getName(): string;

    /**
     * Вызвать роут
     *
     * @param array<string, mixed> $params Параметры
     */
    public function call(array $params, ?Config $config = null): mixed;
}
