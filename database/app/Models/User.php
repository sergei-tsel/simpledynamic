<?php
declare(strict_types=1);
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Сущность "Пользователь"
 *
 * @property int    $id
 * @property string $name
 * @property string $login
 * @property string $password
 * @property string $remember_token
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
 class User extends Model
{
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
}
