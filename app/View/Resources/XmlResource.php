<?php

declare(strict_types=1);

namespace App\View\Resources;

use SimpleXMLElement;

/**
 * XML ресурс
 */
class XmlResource implements ResourceInterface
{
    #[\Override]
    public function serialize(string $data): SimpleXMLElement|false
    {
        return simplexml_load_string($data);
    }

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
