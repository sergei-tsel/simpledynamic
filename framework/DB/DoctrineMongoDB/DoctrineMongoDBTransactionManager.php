<?php

declare(strict_types=1);

namespace Sympledynamic\DB\DoctrineMongoDB;

use Doctrine\ODM\MongoDB\DocumentManager;
use Sympledynamic\DB\TransactionManagerInterface;

/**
 * Сервис для управления транзакцией DoctrineMongoDB
 *
 * @psalm-suppress UnusedClass
 */
final readonly class DoctrineMongoDBTransactionManager implements TransactionManagerInterface
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
        $session = $this->documentManager
            ->getClient()
            ->startSession();
        $session->startTransaction();

        try {
            $result = $todo();

            /** @psalm-suppress InvalidArgument */
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
