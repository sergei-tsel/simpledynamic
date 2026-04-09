<?php

declare(strict_types=1);

namespace Sympledynamic\Base\View;

/**
 * Парсер
 *
 * @psalm-suppress PossiblyUnusedMethod
 */
interface ParserInterface
{
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function serialize(string $data): object|string|false;

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function embed(string $resourceData, array $data): array;
}
