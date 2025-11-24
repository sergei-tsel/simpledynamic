<?php

declare(strict_types=1);

namespace App\Model\ORM\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Модель сущности "Пользователь"
 *
 * @property int $id
 * @property string $name
 * @property string $login
 * @property string $password
 * @property string $remember_token
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class User extends Model
{
    public const string RESOURCE = User::class;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'login',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function __construct(
        public Model|Collection|null $resource = null,
    ) {
        parent::__construct();
    }
}
