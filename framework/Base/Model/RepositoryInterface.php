<?php

declare(strict_types=1);

namespace Sympledynamic\Base\Model;

/**
 * Репозиторий
 *
 * @psalm-suppress UnusedClass
 */
interface RepositoryInterface
{
    public function __construct(
        Mapper $mapper,
    );
}
