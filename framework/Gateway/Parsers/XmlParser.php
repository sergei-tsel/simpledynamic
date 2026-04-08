<?php

declare(strict_types=1);

namespace Framework\Gateway\Parsers;

use Framework\Base\View\ParserInterface;
use SimpleXMLElement;

/**
 * Парсер XML
 */
class XmlParser implements ParserInterface
{
    /**
     * Сериализовать данные
     */
    #[\Override]
    public function serialize(string $data): SimpleXMLElement|false
    {
        return simplexml_load_string($data);
    }

    /**
     * Добавить данные ресурса в данные
     */
    #[\Override]
    public function embed(string $resourceData, array $data): array
    {
        if (!simplexml_load_string($resourceData)->valid()) {
            return $data;
        }

        return array_merge(
            $data,
            get_object_vars(
                simplexml_load_string($resourceData)
            )
        );
    }
}
