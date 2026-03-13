<?php

declare(strict_types=1);

namespace App\Application\Actions;

interface TransactionManagerInterface
{
    public function run(callable $todo): mixed;
}
