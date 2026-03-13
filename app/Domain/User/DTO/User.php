<?php

declare(strict_types=1);

namespace App\Domain\User\DTO;

final readonly class User
{
    public function __construct(
        private int    $id,
        private string $name,
        private string $login,
        private string $hashedPassword,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function getHashedPassword(): string
    {
        return $this->hashedPassword;
    }
}
