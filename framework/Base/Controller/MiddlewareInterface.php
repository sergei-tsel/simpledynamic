<?php

declare(strict_types=1);

namespace Simpledynamic\Base\Controller;

/**
 * Мидлвар
 */
interface MiddlewareInterface
{
    /**
     * Обработать
     *
     * @param array ...$args Аргументы
     */
    public function handle(array ...$args): mixed;
}
