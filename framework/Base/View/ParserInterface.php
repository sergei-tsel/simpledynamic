<?php

declare(strict_types=1);

namespace Simpledynamic\Base\View;

/**
 * Парсер
 */
interface ParserInterface
{
    /**
     * Сериализовать данные
     *
     * @param array $data Данные
     * @return object|array|string|false|null Результат сериализации
     */
    public function serialize(array $data): object|array|string|false|null;

    /**
     * Десериализовать данные
     *
     * @param array $data Данные
     * @return mixed Результат десериализации
     */
    public function deserialize(array $data): mixed;
}
