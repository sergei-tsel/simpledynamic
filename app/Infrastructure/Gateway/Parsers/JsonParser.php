<?php

declare(strict_types=1);

namespace App\Infrastructure\Gateway\Parsers;

/**
 * Парсер JSON
 */
class JsonParser implements ParserInterface
{
    /**
     * Сериализовать
     */
    #[\Override]
    public function serialize(object|array|string $data): string
    {
        return json_encode($data);
    }

    /**
     * Добавить в данные
     */
    #[\Override]
    public function embed(string $resourceData, array $data): array
    {
        if (json_validate($resourceData)) {
            return array_merge($data, json_decode($resourceData, true));
        } else {
            return $data;
        }
    }
}
