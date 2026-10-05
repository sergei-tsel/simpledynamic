<?php

declare(strict_types=1);

namespace Simpledynamic\Integrations;

use Simpledynamic\Base\Model\BuilderInterface;

/**
 * Билдер для транзакций
 */
interface TransactionBuilderInterface extends BuilderInterface
{
    public function run(callable $todo): mixed;
}
