<?php
declare(strict_types=1);
namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Str;

class UserFactory extends BaseFactory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name'           => Str::random(10),
            'login'          => Str::random(10),
            'password'       => hash('sha256', Str::random(10)),
            'remember_token' => Str::random(10),
        ];
    }
}
