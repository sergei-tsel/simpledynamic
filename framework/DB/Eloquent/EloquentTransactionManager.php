<?php

declare(strict_types=1);

namespace Sympledynamic\DB\Eloquent;

use Sympledynamic\DB\TransactionManagerInterface;
use Illuminate\Database\DatabaseManager;

/**
 * Сервис для управления транзакцией Eloquent
 *
 * @psalm-suppress UnusedClass
 */
final readonly class EloquentTransactionManager implements TransactionManagerInterface
{
    public function __construct(
        private DatabaseManager $databaseManager,
    ) {
    }

    /**
     * Выполнить действия с базой данных в транзакции
     */
    #[\Override]
    public function run(callable $todo): mixed
    {
        $this->databaseManager->beginTransaction();

        try {
            $result = $todo();

            $this->databaseManager->commit();

            return $result;
        } catch (\Throwable) {
            $this->databaseManager->rollBack();
        }

        return null;
    }
}
