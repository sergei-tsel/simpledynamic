<?php

declare(strict_types=1);

namespace Test\App\Storage\User;

use Illuminate\Support\Carbon;
use SensitiveParameterValue;
use Simpledynamic\Base\Model\ModelInterface;
use stdClass;

/**
 * Модель сущности "Пользователь"
 *
 * @property ?int                            $id
 * @property string                          $name
 * @property string                          $email
 * @property SensitiveParameterValue<string> $password
 * @property ?Carbon                         $createdAt
 * @property ?Carbon                         $updatedAt
 */
class UserModel implements ModelInterface
{
    public protected(set) ?int $id;

    public string $name;

    public string $email;

    public SensitiveParameterValue $password;

    public protected(set) ?Carbon $createdAt;

    public protected(set) ?Carbon $updatedAt;

    /**
     * @param stdClass|array{
     *     id?:         int,
     *     name:        string,
     *     email:       string,
     *     password:    string,
     *     created_at?: string,
     *     updated_at?: string,
     * } $attributes
     */
    public function __construct(stdClass|array $attributes)
    {
        if ($attributes instanceof stdClass) {
            /**
             * @var array{
             *     id?:         int,
             *     name:        string,
             *     email:       string,
             *     password:    string,
             *     created_at?: string,
             *     updated_at?: string,
             * }
             */
            $attributes = (array) $attributes;
        }

        $this->id = $attributes['id'] ?? null;
        $this->name = $attributes['name'];
        $this->email = $attributes['email'];
        $this->password = new SensitiveParameterValue($attributes['password']);
        $this->createdAt = Carbon::parse($attributes['created_at'] ?? null);
        $this->updatedAt = Carbon::parse($attributes['updated_at'] ?? null);
    }
}
