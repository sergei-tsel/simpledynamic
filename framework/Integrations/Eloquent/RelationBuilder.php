<?php

declare(strict_types=1);

namespace Simpledynamic\Integrations\Eloquent;

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Query\Builder;
use Simpledynamic\Integrations\SaveBuilderInterface;
use Simpledynamic\Integrations\WriteBuilderInterface;
use stdClass;

/**
 * Билдер для работы с реляционной базой данных
 */
final readonly class RelationBuilder implements SaveBuilderInterface, WriteBuilderInterface
{
    public function __construct(
        private DatabaseManager $database,
    ) {}

    /**
     * Найти запись
     */
    #[\Override]
    public function find(array $params, string $table = ''): ?stdClass
    {
        $query = $this->buildQuery($table, $params);

        return $query?->first();
    }

    /**
     * Найти множество записей
     */
    #[\Override]
    public function findMany(array $params, array $pagination = [], string $table = ''): array
    {
        $query = $this->buildQuery($table, $params);

        if ($query === null) {
            return [];
        }

        if (array_key_exists('limit', $pagination)) {
            $query = $query->limit((int) $pagination['limit']);
        }

        if (array_key_exists('offset', $pagination)) {
            $query = $query->offset((int) $pagination['offset']);
        }

        $results = $query->get();

        return $results->all();
    }

    /**
     * Проверить существование записи
     */
    #[\Override]
    public function exists(array $params, string $table = ''): bool
    {
        $query = $this->buildQuery($table, $params);

        return $query !== null && $query->exists();
    }

    /**
     * Создать запись
     */
    #[\Override]
    public function create(array $data, string $table = ''): int
    {
        if ($table === '') {
            return 0;
        }

        return $this->database->query()->from($table)->insertGetId($data);
    }

    /**
     * Изменить запись
     */
    #[\Override]
    public function update(array $params, array $data, string $table = ''): void
    {
        $query = $this->buildQuery($table, $params);

        $query?->update($data);
    }

    /**
     * Удалить запись
     */
    #[\Override]
    public function delete(array $params, string $table = ''): void
    {
        $query = $this->buildQuery($table, $params);

        $query?->delete();
    }

    /**
     * Построить запрос с параметрами
     *
     * @param array<string, mixed> $params Параметры поиска
     * @return Builder|null
     */
    private function buildQuery(string $table, array $params): ?Builder
    {
        if ($table === '') {
            return null;
        }

        $query = $this->database->query()->from($table);

        /** @var mixed $value */
        foreach ($params as $column => $value) {
            $this->validateColumnName($column);

            if ($value === null) {
                $query = $query->whereNull($column);
                continue;
            }

            $query = $query->where($column, $value);
        }

        return $query;
    }

    /**
     * Валидировать название колонки
     *
     * @param string $column Название колонки
     * @throws \InvalidArgumentException
     */
    private function validateColumnName(string $column): void
    {
        if ($column === '' || !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column)) {
            throw new \InvalidArgumentException("Invalid column name: {$column}");
        }
    }
}
