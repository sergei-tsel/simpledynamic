<?php
declare(strict_types=1);
namespace App\Model\ODM\Repositories;

use App\Model\ODM\Documents\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Iterator\Iterator;

/**
 * Репозиторий для документа сущности "Пользователь"
 */
class UserRepository
{
    protected const string DOCUMENT = User::class;

    public function __construct(
        public DocumentManager $documentManager,
    )
    {
    }

    public function getAllByLogin(string $login, ?string $document = self::DOCUMENT): Iterator
    {
        $builder = $this->documentManager->createQueryBuilder($document)
            ->field('login')
            ->equals($login);

        return $builder->getQuery()->execute();
    }
}
