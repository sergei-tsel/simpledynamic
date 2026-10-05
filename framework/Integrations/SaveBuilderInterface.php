<?php

declare(strict_types=1);

namespace Simpledynamic\Integrations;

use Simpledynamic\Base\Model\BuilderInterface;
use stdClass;

/**
 * Билдер для чтения данных
 */
interface SaveBuilderInterface extends BuilderInterface
{
    /**
     * Найти
     *
     * @param array<string, scalar|null> $params Параметры поиска
     */
    public function find(array $params): ?stdClass;

    /**
     * Найти множество
     *
     * @param array<string, scalar|null> $params Параметры поиска
     * @param array<string, int|null> $pagination Параметры пагинации
     * @return array<array|stdClass> Результаты поиска по параметрам
     */
    public function findMany(array $params, array $pagination = []): array;

    /**
     * Проверить существование
     *
     * @param array<string, scalar|null> $params Параметры поиска
     */
    public function exists(array $params): bool;
}
