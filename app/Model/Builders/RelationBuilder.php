<?php

declare(strict_types=1);

namespace App\Model\Builders;

/**
 * Создатель репозитрия для ORM
 */
class RelationBuilder implements BuilderInterface
{
    #[\Override]
    public function createRepository(string $entity): object
    {
        return new $entity();
    }
}
