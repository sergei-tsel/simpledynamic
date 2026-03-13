<?php

declare(strict_types=1);

namespace App\Infrastructure\Storage\User\Persistence\DoctrineMongoDB;

use App\Domain\User\Contracts\UserProviderInterface;
use App\Domain\User\DTO\User;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Репозиторий для документа сущности "Пользователь"
 */
readonly class UserDocumentRepository implements UserProviderInterface
{
    public function __construct(
        private DocumentManager    $documentManager,
        private UserDocumentMapper $userMapper,
    ) {
    }

    public function getOneByLogin(string $login): User
    {
        /** @var UserDocument $document */
        $document = $this->documentManager->createQueryBuilder(UserDocument::class)
            ->field('login')
            ->equals($login)
            ->getQuery()
            ->getSingleResult();

        return $this->userMapper->modelToDto($document);
    }
}
