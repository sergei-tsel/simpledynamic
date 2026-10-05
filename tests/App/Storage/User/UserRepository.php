<?php

declare(strict_types=1);

namespace Test\App\Storage\User;

use Simpledynamic\Base\Model\RepositoryInterface;

/**
 * Репозиторий сущности "Пользователь"
 */
readonly class UserRepository implements RepositoryInterface
{
    public function __construct(
        private UserBuilder $builder,
    ) {}

    /**
     * Найти пользователя по id
     */
    public function findById(int $userId): ?UserModel
    {
        $data = $this->builder->find(userId: $userId);

        if ($data === null) {
            return null;
        }

        return new UserModel(attributes: $data);
    }

    /**
     * Найти пользователя по email
     */
    public function findByEmail(string $email): ?UserModel
    {
        $data = $this->builder->find(email: $email);

        if ($data === null) {
            return null;
        }

        return new UserModel(attributes: $data);
    }

    /**
     * Создать пользователя
     */
    public function create(UserModel $user): ?UserModel
    {
        /** @var string $password */
        $password = $user->password->getValue();

        $modelId = $this->builder->create(user: [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $password,
        ]);

        return $this->findById(userId: $modelId);
    }

    /**
     * Изменить email пользователя
     */
    public function updateEmail(UserModel $user, string $newEmail): ?UserModel
    {
        if ($user->id === null) {
            return null;
        }

        $this->builder->updateEmail(user: [
            'email' => $user->email,
        ], newEmail: $newEmail);

        return $this->findById(userId: $user->id);
    }

    /**
     * Удалить пользователя
     */
    public function delete(UserModel $user): void
    {
        if ($user->id === null) {
            return;
        }

        $this->builder->delete(user: [
            'id' => $user->id,
        ]);
    }

    /**
     * Проверить существование пользователя по email
     */
    public function existsByEmail(string $email): bool
    {
        return $this->builder->exists(user: [
            'email' => $email,
        ]);
    }
}
