<?php

declare(strict_types=1);

namespace Simpledynamic\Integrations\DoctrineMongo;

use Doctrine\ODM\MongoDB\DocumentManager;
use Simpledynamic\Integrations\TransactionBuilderInterface;
use Simpledynamic\Services\Logging\Logger;

/**
 * Билдер для транзакций докуметов DoctrineMongoDB
 */
final readonly class DocumentTransactionBuilder implements TransactionBuilderInterface
{
    public function __construct(
        private DocumentManager $documentManager,
    ) {}

    /**
     * Выполнить действия с базой данных в транзакции
     */
    #[\Override]
    public function run(callable $todo): mixed
    {
        $session = $this->documentManager->getClient()->startSession();
        $session->startTransaction();

        try {
            /**
             * @var mixed $result
             */
            $result = $todo();

            /**
             * @var array{'w'?: int, 'withTransaction'?: bool, 'writeConcern'?: \MongoDB\Driver\WriteConcern} $options
             */
            $options = [
                'session' => $session,
            ];

            $this->documentManager->flush($options);
            $session->commitTransaction();

            return $result;
        } catch (\Throwable $exception) {
            // Откат возвращает null в пользу вызывающего, поэтому причина сбоя
            // транзакции остаётся только здесь
            Logger::report($exception);
            $session->abortTransaction();

            return null;
        } finally {
            $session->endSession();
        }
    }
}
