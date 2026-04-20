<?php

declare(strict_types=1);

namespace Simpledynamic\Integrations;

/**
 * Сервис для управления транзакцией
 *
 * @psalm-suppress PossiblyUnusedMethod
 */
interface TransactionManagerInterface
{
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function run(callable $todo): mixed;
}
