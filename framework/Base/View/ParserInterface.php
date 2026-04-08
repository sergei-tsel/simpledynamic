<?php

declare(strict_types=1);

namespace Framework\Base\View;

/**
 * Парсер
 */
interface ParserInterface
{
    public function serialize(string $data): object|string|false;

    public function embed(string $resourceData, array $data): array;
}
