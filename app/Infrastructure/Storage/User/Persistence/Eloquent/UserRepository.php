<?php

declare(strict_types=1);

namespace App\Infrastructure\Storage\User\Persistence\Eloquent;

use App\Domain\User\Contracts\UserProviderInterface;
use App\Domain\User\DTO\User;

/**
 * Репозиторий сущности "Пользователь"
 */
readonly class UserRepository implements UserProviderInterface
{
    public function __construct(
        private UserMapper $mapper,
    ) {
    }

    /**
     * Получить пользователя по логину
     */
    public function getOneByLogin(string $login): ?User
    {
        $model = UserModel::query()
            ->where('login', $login)
            ->first();

        if ($model instanceof UserModel) {
            return $this->mapper->modelToDto($model);
        }

        return null;
    }
}
