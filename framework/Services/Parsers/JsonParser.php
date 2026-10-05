<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Parsers;

use Simpledynamic\Base\View\ParserInterface;

/**
 * Парсер JSON
 */
final class JsonParser implements ParserInterface
{
    /**
     * Сериализовать данные
     *
     * @param array $data Данные
     */
    #[\Override]
    public function serialize(array $data): string|false
    {
        return json_encode($data);
    }

    /**
     * Десериализовать данные
     *
     * @param array $data Данные
     * @return mixed Результат десериализации
     */
    #[\Override]
    public function deserialize(array $data): mixed
    {
        $json = json_encode($data);

        if ($json === false) {
            return null;
        }

        return json_decode($json, true);
    }

    /**
     * Добавить данные ресурса в данные
     *
     * @param array $data Данные
     * @return array
     */
    public function embed(string $resourceData, array $data): array
    {
        if (!json_validate($resourceData)) {
            return $data;
        }

        /** @var array|false|null $decoded */
        $decoded = json_decode($resourceData, true);

        if (!is_array($decoded)) {
            return $data;
        }

        return array_merge($data, $decoded);
    }
}
