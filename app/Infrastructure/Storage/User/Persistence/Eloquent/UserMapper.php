<?php

declare(strict_types=1);

namespace App\Infrastructure\Storage\User\Persistence\Eloquent;

use App\Domain\User\DTO\User;

/**
 * Маппер сущности "Пользователь"
 */
class UserMapper
{
    public function modelToDto(UserModel $model): User
    {
        return new User(
            id: $model->id,
            name: $model->name,
            login: $model->login,
            hashedPassword: $model->password,
        );
    }
}
