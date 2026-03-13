<?php

declare(strict_types=1);

namespace App\Infrastructure\Storage\User\Persistence\DoctrineMongoDB;

use Doctrine\ODM\MongoDB\Mapping\Attribute\Document;
use Doctrine\ODM\MongoDB\Mapping\Attribute\Field;
use Doctrine\ODM\MongoDB\Mapping\Attribute\Id;

#[Document]
/**
 * Документ сущности "Пользователь"
 */
class UserDocument
{
    #[Id(type: 'id')]
    public int $id;

    public function __construct(
        #[Field(type: 'name')] public string $name,
        #[Field(type: 'string')] public string $login,
        #[Field(type: 'password')] public string $password,
    ) {
    }
}
