<?php

declare(strict_types=1);

namespace App\Infrastructure\Storage\User\Persistence\DoctrineMongoDB;

use App\Domain\User\DTO\User;

/**
 * Маппер для документа сущности "Пользователь"
 */
class UserDocumentMapper
{
    public function modelToDto(UserDocument $document): User
    {
        return new User(
            id: $document->id,
            name: $document->name,
            login: $document->login,
            hashedPassword: $document->password,
        );
    }
}
