<?php
declare(strict_types=1);
namespace App\View\Resources;

use App\View\Resources\ResourceInterface;

/**
 * JSON ресурс
 */
class JsonResource implements ResourceInterface
{
    /**
     * Сереализовать
     */
    public function serialize(object|array|string $data): string
    {
        return json_encode($data);
    }

    /**
     * Добавить в данные
     */
    public function embed(string $resourceData, array $data): array
    {
        if (json_validate($resourceData)) {
            return array_merge($data, json_decode($resourceData, true));
        } else {
            return $data;
        }
    }
}