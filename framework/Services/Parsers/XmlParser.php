<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Parsers;

use SimpleXMLElement;
use Simpledynamic\Base\View\ParserInterface;

/**
 * Парсер XML
 *
 * @psalm-suppress UnusedClass
 */
final class XmlParser implements ParserInterface
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
        $xmlElement = simplexml_load_string($resourceData);

        if ($xmlElement === false || !$xmlElement->valid()) {
            return $data;
        }

        return array_merge($data, get_object_vars($xmlElement));
    }
}
