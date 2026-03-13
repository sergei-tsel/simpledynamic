<?php

declare(strict_types=1);

namespace App\Infrastructure\Gateway\Parsers;

use SimpleXMLElement;

/**
 * XML ресурс
 */
class XmlParser implements ParserInterface
{
    /**
     * Сериализовать
     */
    #[\Override]
    public function serialize(string $data): SimpleXMLElement|false
    {
        return simplexml_load_string($data);
    }

    /**
     * Добавить в данные
     */
    #[\Override]
    public function embed(string $resourceData, array $data): array
    {
        if (simplexml_load_string($resourceData)->valid()) {
            return array_merge(
                $data,
                get_object_vars(
                    simplexml_load_string($resourceData)
                )
            );
        } else {
            return $data;
        }
    }
}
