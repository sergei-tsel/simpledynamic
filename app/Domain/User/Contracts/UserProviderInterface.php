<?php

declare(strict_types=1);

namespace App\Domain\User\Contracts;

use App\Domain\User\DTO\User;

interface UserProviderInterface
{
    public function getOneByLogin(string $login): ?User;
}
