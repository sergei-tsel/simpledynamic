<?php

declare(strict_types=1);

namespace App\Model\Builders;

use config\ORM;

/**
 * Создатель репозитория для ORM
 */
class RelationBuilder implements BuilderInterface
{
    #[\Override]
    public function createRepository(string $entity): object
    {
        ORM::createEloquentConfig();

        return new $entity();
    }
}
