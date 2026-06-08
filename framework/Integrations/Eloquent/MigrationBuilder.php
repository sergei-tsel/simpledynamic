<?php

declare(strict_types=1);

namespace Simpledynamic\Integrations\Eloquent;

use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;

/**
 * Создатель запросов для запуска миграций
 *
 * @psalm-suppress UnusedClass
 */
final class MigrationBuilder
{
    /**
     * @psalm-suppress PossiblyUnusedMethod
     * @param DatabaseManager $manager
     */
    public function __construct(
        public DatabaseManager $manager,
    ) {
    }

    /**
     * Получить миграции
     *
     * @return Collection<int, \stdClass>
     */
    public function getAll(): Collection
    {
        return $this->manager->query()
            ->from('migrations')
            ->get();
    }

    /**
     * Создать миграцию
     *
     * @param array{instance: object, name: string, batch: int} $newMigration
     */
    public function create(array $newMigration): void
    {
        if (!method_exists($newMigration['instance'], 'up')) {
            return;
        }

        /** @psalm-suppress MixedMethodCall */
        $newMigration['instance']->up($this->manager->getSchemaBuilder());

        $this->manager->query()
            ->from('migrations')
            ->insert([
                'name'  => $newMigration['name'],
                'batch' => $newMigration['batch'],
            ]);
    }

    /**
    * Удалить миграцию
    */
    public function delete(string $directory, string $name): void
    {
        $filePath = $directory . '/' . $name . '.php';

        if (!file_exists($filePath)) {
            return;
        }

        /** @var object $migration */
        $migration = include_once $filePath;

        if (!method_exists($migration, 'down')) {
            return;
        }

        /** @psalm-suppress MixedMethodCall */
        $migration->down($this->manager->getSchemaBuilder());

        if (str_contains($name, 'create_migrations_table')) {
            return;
        }

        $this->manager->query()
            ->from('migrations')
            ->where('name', $name)
            ->delete();
    }

    /**
     * Проверить существование таблицы миграций
     */
    public function tableExists(): bool
    {
        return (bool) $this->manager->query()
            ->selectRaw("to_regclass('public.migrations') IS NOT NULL AS exists")
            ->value('exists');
    }
}
