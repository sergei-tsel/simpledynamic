<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Parsers;

use Simpledynamic\Base\View\ParserInterface;

/**
 * Парсер JSON
 *
 * @psalm-suppress UnusedClass
 */
final class JsonParser implements ParserInterface
{
    /**
     * Сериализовать данные
     */
    #[\Override]
    public function serialize(object|array|string $data): string|false
    {
        return json_encode($data);
    }

    /**
     * Добавить данные ресурса в данные
     */
    #[\Override]
    public function embed(string $resourceData, array $data): array
    {
        if (!json_validate($resourceData)) {
            return $data;
        }

        return array_merge($data, (array) json_decode($resourceData, true));
    }
}
