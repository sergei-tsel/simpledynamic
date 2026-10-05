<?php

declare(strict_types=1);

namespace Test\App\Storage\User;

use Illuminate\Database\DatabaseManager;
use Simpledynamic\Base\Model\BuilderInterface;
use stdClass;

/**
 * Билдер сущности "Пользователь"
 */
class UserBuilder implements BuilderInterface
{
    public function __construct(
        private readonly DatabaseManager $manager,
    ) {}

    /**
     * Найти пользователя
     */
    public function find(?int $userId = null, ?string $email = null): ?stdClass
    {
        if ($userId === null && $email === null) {
            return null;
        }

        $param = [];

        if ($userId !== null) {
            $param['id'] = $userId;
        }

        if ($email !== null) {
            $param['email'] = $email;
        }

        return $this->manager->query()->from('users')->where($param)->first();
    }

    /**
     * Создать пользователя
     *
     * @param array{
     *     name:     string,
     *     email:    string,
     *     password: string,
     * } $user
     */
    public function create(array $user): int
    {
        return $this->manager->query()->from('users')->insertGetId($user);
    }

    /**
     * Изменить email пользователя
     *
     * @param array{id?: int, email?: string} $user
     */
    public function updateEmail(array $user, string $newEmail): void
    {
        $param = $this->getSearchParam(user: $user);

        $this->manager
            ->query()
            ->from('users')
            ->where($param)
            ->update([
                'email' => $newEmail,
            ]);
    }

    /**
     * Удалить пользователя
     *
     * @param array{id?: int, email?: string} $user
     */
    public function delete(array $user): void
    {
        $param = $this->getSearchParam(user: $user);

        $this->manager->query()->from('users')->where($param)->delete();
    }

    /**
     * Проверить существование пользователя
     *
     * @param array{id?: int, email?: string} $user
     */
    public function exists(array $user): bool
    {
        $param = $this->getSearchParam(user: $user);

        return $this->manager->query()->from('users')->where($param)->exists();
    }

    /**
     * Получить параметры поиска пользователя
     *
     * @param array{id?: int, email?: string} $user
     * @return array<string, int|string>
     */
    private function getSearchParam(array $user): array
    {
        $param = [];

        if (array_key_exists('id', $user)) {
            $param['id'] = $user['id'];
        }

        if (array_key_exists('email', $user)) {
            $param['email'] = $user['email'];
        }

        return $param;
    }
}
