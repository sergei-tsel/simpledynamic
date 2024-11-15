<?php
declare(strict_types=1);
namespace App\Model\ORM\Repositories;

use App\Model\ORM\Models\User;

/**
 * Репозиторий сущности "Пользователь"
 */
class UserRepository
{
    protected const MODEL = User::class;

    public function getPasswordByLogin(string $login): ?string
    {
        /** @var User $user */
        $user = new self::MODEL();

        $resource = $user->resource::query()
            ->where(['login' => $login])
            ->first();

        return $resource?->password;
    }
}
