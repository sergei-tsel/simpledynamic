<?php

declare(strict_types=1);

namespace App\Model\ODM\Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations\Document;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Field;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Id;

#[Document]
/**
 * Документ сущности "Пользователь"
 */
class User
{
    #[Id(type: 'string')]
    public string $id;

    public function __construct(
        #[Field(type: 'string')] public string $login,
    ) {
    }
}
