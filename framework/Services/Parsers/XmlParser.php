<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Parsers;

use Simpledynamic\Base\View\ParserInterface;
use SimpleXMLElement;

/**
 * Парсер XML
 */
final class XmlParser implements ParserInterface
{
    /**
     * Сериализовать данные
     *
     * @param array $data Данные
     */
    #[\Override]
    public function serialize(array $data): SimpleXMLElement|false
    {
        $json = json_encode($data);

        if ($json === false) {
            return false;
        }

        return simplexml_load_string($json);
    }

    /**
     * Десериализовать данные
     *
     * @param array $data Данные
     * @return object|array|string|false|null Результат десериализации
     */
    #[\Override]
    public function deserialize(array $data): object|array|string|false|null
    {
        $json = json_encode($data);

        if ($json === false) {
            return null;
        }

        $xmlElement = simplexml_load_string($json);

        if ($xmlElement === false || !$xmlElement->valid()) {
            return null;
        }

        return get_object_vars($xmlElement);
    }

    /**
     * Добавить данные ресурса в данные
     *
     * @param array $data Данные
     * @return array
     */
    public function embed(string $resourceData, array $data): array
    {
        $xmlElement = simplexml_load_string($resourceData);

        if ($xmlElement === false || !$xmlElement->valid()) {
            return $data;
        }

        return array_merge($data, get_object_vars($xmlElement));
    }
}
