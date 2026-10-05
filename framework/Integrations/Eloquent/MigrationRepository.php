<?php

declare(strict_types=1);

namespace Simpledynamic\Integrations\Eloquent;

use Illuminate\Support\Collection;
use Simpledynamic\Base\Model\RepositoryInterface;

/**
 * Репозиторий миграций
 *
 * Поиск файлов миграций делегирован {@see MigrationLocator} — единый механизм
 * используется и при прокатке (run), и при откате (rollback).
 */
final class MigrationRepository implements RepositoryInterface
{
    private ?Collection $migrations = null;

    /** @var array<int, array{instance: object, name: string, batch: int}> */
    private array $newMigrations = [];

    private MigrationLocator $locator;

    public function __construct(
        public MigrationBuilder $builder,
    ) {
        $this->locator = new MigrationLocator();

        if ($builder->tableExists()) {
            $this->migrations = $builder->getAll();
        }
    }

    /**
     * Запустить миграции
     */
    public function run(): void
    {
        $this->collectNewMigrations();

        if ($this->newMigrations === []) {
            return;
        }

        uksort($this->newMigrations, static fn(int $a, int $b): int => $a <=> $b);

        foreach ($this->newMigrations as $newMigration) {
            $this->builder->create(newMigration: $newMigration);
        }
    }

    /**
     * Откатить миграции
     */
    public function rollback(?int $lastCount = null): void
    {
        if ($this->migrations === null) {
            return;
        }

        $names = $this->migrations->sortByDesc('name')->pluck('name');
        $count = $names->count();
        $slice = $names->slice(0, $lastCount ?? $count)->all();

        /** @var string[] $slice */
        $this->deleteMigrations($slice);
    }

    /**
     * Удалить миграции с указанными именами
     *
     * @param string[] $names
     */
    private function deleteMigrations(array $names): void
    {
        foreach ($names as $name) {
            $this->locator->each(function (string $directory, string $fileName) use ($name): void {
                if ($fileName !== $name) {
                    return;
                }

                $this->builder->delete(directory: $directory, name: $name);
            });
        }
    }

    /**
     * Собрать новые миграции из директорий
     */
    private function collectNewMigrations(): void
    {
        $this->locator->each(function (string $directory, string $name): void {
            if ($this->migrations !== null && $this->migrations->contains('name', $name)) {
                return;
            }

            /** @var object $migration */
            $migration = include_once $directory . '/' . $name . '.php';

            $this->newMigrations[$this->sortableKey($name)] = [
                'instance' => $migration,
                'name' => $name,
                'batch' => $this->nextBatch(),
            ];
        });
    }

    /**
     * Получить целочисленный ключ сортировки из имени миграции
     */
    private function sortableKey(string $name): int
    {
        return (int) (str_replace('_', '', substr($name, 0, 10)) . substr($name, 11));
    }

    /**
     * Получить номер следующего батча
     */
    private function nextBatch(): int
    {
        if ($this->migrations === null) {
            return 1;
        }

        return (int) $this->migrations->max('batch') + 1;
    }
}
