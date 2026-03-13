<?php

declare(strict_types=1);

namespace App\Framework\Services\DB\DoctrineMongoDB;

use App\Application\Actions\TransactionManagerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;

readonly class DoctrineMongoDBTransactionManager implements TransactionManagerInterface
{
    public function __construct(
        private DocumentManager $documentManager,
    ) {
    }

    /**
     * Выполнить действия с базой данных в транзакции
     *
     * @throws \Throwable
     */
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
        } catch (\Throwable $exception) {
            $session->abortTransaction();

            throw $exception;
        } finally {
            $session->endSession();
        }
    }
}
