<?php

declare(strict_types=1);

namespace App\Model\ORM\Repositories;

use App\Model\ORM\Models\User;
use App\Models\User as EloquentUser;

/**
 * Репозиторий сущности "Пользователь"
 */
class UserRepository
{
    protected const string MODEL = User::class;

    public function getPasswordByLogin(string $login): ?string
    {
        /** @var User $user */
        $user = new (self::MODEL)();

        /** @var EloquentUser $eloquentUser */
        $eloquentUser = new ($user::RESOURCE)();

        $resource = $eloquentUser::query()
            ->where(['login' => $login])
            ->first();

        return $resource?->password;
    }
}
