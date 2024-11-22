<?php

declare(strict_types=1);

namespace App\View\Resources;

/**
 * Интерфейс ресурса
 */
interface ResourceInterface
{
    public function serialize(string $data);

    public function embed(string $resourceData, array $data);
}
