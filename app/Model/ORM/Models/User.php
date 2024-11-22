<?php

declare(strict_types=1);

namespace App\Model\ORM\Models;

use App\Models\User as EloquentUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Модель сущности "Пользователь"
 */
class User
{
    public const string RESOURCE = EloquentUser::class;

    public function __construct(
        public Model|Collection|null $resource = null,
    ) {
    }
}
