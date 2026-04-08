<?php

declare(strict_types=1);

namespace Framework\Base\Model;

/**
 * Репозиторий
 */
interface RepositoryInterface
{
    public function __construct(
        Mapper $mapper,
    );
}
