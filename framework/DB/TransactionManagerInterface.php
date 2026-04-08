<?php

declare(strict_types=1);

namespace Framework\DB;

/**
 * Сервис для управления транзакцией
 */
interface TransactionManagerInterface
{
    public function run(callable $todo): mixed;
}
