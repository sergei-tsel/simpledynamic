<?php
declare(strict_types=1);
namespace App\Model\Builders;

use App\Model\Builders\BuilderInterface;

/**
 * Создатель репозитрия для ORM
 */
class RelationBuilder implements BuilderInterface
{
    public function createRepository(string $entity): object
    {
        return new $entity();
    }
}
