<?php

declare(strict_types=1);

namespace Simpledynamic\Integrations\Eloquent;

use Illuminate\Support\Collection;
use Simpledynamic\Services\Configuration\ORM;

/**
 * Сервис для запуска миграций
 *
 * @psalm-suppress UnusedClass
 */
final class MigrationRepository
{
    private ?Collection $migrations = null;

    /** @var array<int, array{instance: object, name: string, batch: int}> */
    private array $newMigrations = [];

    public function __construct(
        public MigrationBuilder $builder,
    ) {
        $tableExits = $builder->tableExists();

        if ($tableExits) {
            $this->migrations = $builder->getAll();
        }
    }

    /**
     * Загрузить миграции из директории
     */
    public function load(string $directory): void
    {
        /** @var array|false $files */
        $files = scandir($directory);
        $realDir = realpath($directory);

        if (!is_array($files) || !is_string($realDir)) {
            return;
        }

        foreach ($files as $file) {
            $matches = [];

            if (!is_string($file) || !preg_match('/^(\d{4}_\d{2}_\d{2}_\d{6})_(.*)_table\.php$/', $file, $matches)) {
                continue;
            }

            $name = basename($file, '.php');
            $path = realpath($directory . '/' . $file);

            if (!is_string($path) || !str_starts_with($path, $realDir) || ($this->migrations !== null && $this->migrations->contains('name', $name))) {
                continue;
            }

            /**
             * @psalm-suppress UnresolvableInclude
             * @var object $migration
             */
            $migration = include_once $path;

            $sortable = str_replace('_', '', substr($matches[1], 0, 10)) . substr($matches[1], 11);

            $this->newMigrations[(int) $sortable] = [
                'instance' => $migration,
                'name'     => $name,
                'batch'    => $this->migrations === null ? 1 : (int) $this->migrations->max('batch') + 1,
            ];
        }
    }

    /**
     * Запустить миграции
     */
    public function run(): void
    {
        /**
         * @psalm-suppress UndefinedMagicMethod
         * @var array $directories
         */
        $directories = ORM::getMigrationDirectories();

        if ($directories === []) {
            return;
        }

        foreach ($directories as $directory) {
            if (!is_string($directory)) {
                continue;
            }

            $this->load(directory: $directory);
        }

        if ($this->newMigrations === []) {
            return;
        }

        uksort($this->newMigrations, fn (int $a, int $b): int => $a <=> $b);

        foreach ($this->newMigrations as $newMigration) {
            $this->builder->create(newMigration: $newMigration);
        }
    }

    /**
     * Откатить все миграции
     */
    public function rollback(?int $lastCount = null, bool $hasMaxBatch = false): void
    {
        if ($this->migrations === null) {
            return;
        }

        $migrations = $hasMaxBatch
            ? $this->migrations->where('batch', $this->migrations->max('batch'))
            : $this->migrations;

        /** @var Collection<array-key, string> $names */
        $names = $migrations
            ->sortByDesc('name')
            ->pluck('name');

        if ($names->count() === 0 || ($lastCount !== null && ($lastCount < 1 || $lastCount > $names->count()))) {
            return;
        }

        $count = $lastCount ?? $names->count();

        /**
         * @psalm-suppress UndefinedMagicMethod
         * @var string[] $directories
         */
        $directories = ORM::getMigrationDirectories();

        for ($i = 0; $i < $count; $i++) {
            foreach ($directories as $directory) {
                $this->builder->delete(directory: $directory, name: $names[$i]);
            }
        }
    }
}
