<?php

declare(strict_types=1);

namespace Sympledynamic\Gateway\Parsers;

use Sympledynamic\Base\View\ParserInterface;

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

        return array_merge($data, json_decode($resourceData, true));
    }
}
