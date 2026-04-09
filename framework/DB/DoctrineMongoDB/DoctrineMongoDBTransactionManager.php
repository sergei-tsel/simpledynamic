<?php

declare(strict_types=1);

namespace Framework\DB\DoctrineMongoDB;

use Doctrine\ODM\MongoDB\DocumentManager;
use Framework\DB\TransactionManagerInterface;

/**
 * Сервис для управления транзакцией DoctrineMongoDB
 */
readonly class DoctrineMongoDBTransactionManager implements TransactionManagerInterface
{
    public function __construct(
        private DocumentManager $documentManager,
    ) {
    }

    /**
     * Выполнить действия с базой данных в транзакции
     */
    #[\Override]
    public function run(callable $todo): mixed
    {
        $session = $this->documentManager->getClient()->startSession();
        $session->startTransaction();

        try {
            $result = $todo();

            $this->documentManager->flush([
                'session' => $session,
            ]);
            $session->commitTransaction();

            return $result;
        } catch (\Throwable) {
            $session->abortTransaction();
        } finally {
            $session->endSession();
        }

        return null;
    }
}
