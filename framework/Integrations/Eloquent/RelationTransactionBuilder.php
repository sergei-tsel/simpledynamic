<?php

declare(strict_types=1);

namespace Simpledynamic\Integrations\Eloquent;

use Illuminate\Database\DatabaseManager;
use Simpledynamic\Integrations\TransactionBuilderInterface;
use Simpledynamic\Services\Logging\Logger;

/**
 * Билдер для реляционных транзакций в Eloquent
 */
final readonly class RelationTransactionBuilder implements TransactionBuilderInterface
{
    public function __construct(
        private DatabaseManager $databaseManager,
    ) {}

    /**
     * Выполнить действия с базой данных в транзакции
     */
    #[\Override]
    public function run(callable $todo): mixed
    {
        try {
            $this->databaseManager->beginTransaction();
        } catch (\Throwable $exception) {
            Logger::report($exception);

            return null;
        }

        try {
            /**
             * @var mixed $result
             */
            $result = $todo();

            $this->databaseManager->commit();

            return $result;
        } catch (\Throwable $exception) {
            Logger::report($exception);

            try {
                $this->databaseManager->rollBack();

                return null;
            } catch (\Throwable $rollbackException) {
                // Исходное исключение уже записано, здесь теряется только причина
                // неудачного отката, из-за которого соединение осталось в транзакции
                Logger::report($rollbackException);

                return null;
            }
        }
    }
}
