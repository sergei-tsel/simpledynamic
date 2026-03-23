<?php

declare(strict_types=1);

namespace App\Infrastructure\Storage\User\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Модель сущности "Пользователь"
 *
 * @property int $id
 * @property string $name
 * @property string $login
 * @property string $password
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Table('users')]
#[Fillable([
    'name',
    'login',
    'password'
])]
#[Hidden(['password'])]
class UserModel extends Model
{
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
