<?php

declare(strict_types=1);

namespace App\Framework\Services\DB\Eloquent;

use App\Application\Actions\TransactionManagerInterface;
use Illuminate\Database\DatabaseManager;

readonly class EloquentTransactionManager implements TransactionManagerInterface
{
    public function __construct(
        private DatabaseManager $databaseManager,
    ) {
    }

    /**
     * Выполнить действия с базой данных в транзакции
     *
     * @throws \Throwable
     */
    #[\Override]
    public function run(callable $todo): mixed
    {
        $this->databaseManager->beginTransaction();

        try {
            $result = $todo();

            $this->databaseManager->commit();

            return $result;
        } catch (\Throwable $exception) {
            $this->databaseManager->rollBack();

            throw $exception;
        }
    }
}
