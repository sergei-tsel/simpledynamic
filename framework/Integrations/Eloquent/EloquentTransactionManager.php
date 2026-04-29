<?php

declare(strict_types=1);

namespace Simpledynamic\Integrations\Eloquent;

use Illuminate\Database\DatabaseManager;
use Simpledynamic\Integrations\TransactionManagerInterface;

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
        try {
            $this->databaseManager->beginTransaction();
        } catch (\Throwable) {
            return null;
        }

        try {
            /**
             * @psalm-suppress MixedAssignment
             * @var mixed $result
             */
            $result = $todo();

            $this->databaseManager->commit();

            return $result;
        } catch (\Throwable) {
            try {
                $this->databaseManager->rollBack();

                return null;
            } catch (\Throwable) {
                return null;
            }
        }
    }
}
