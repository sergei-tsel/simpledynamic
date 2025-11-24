<?php

declare(strict_types=1);

namespace App\Model\ORM\Repositories;

use App\Model\ORM\Models\User;

/**
 * Репозиторий сущности "Пользователь"
 */
class UserRepository
{
    protected const string MODEL = User::class;

    public function getPasswordByLogin(string $login): ?string
    {
        $user = self::MODEL::query()
            ->where(['login' => $login])
            ->first();

        return $user?->password;
    }
}
