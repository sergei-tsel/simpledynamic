<?php
declare(strict_types=1);
namespace App\Model\Builders;

/**
 * Интерфейс создателя репозитория
 */
interface BuilderInterface
{
    public function createRepository($entity);
}
