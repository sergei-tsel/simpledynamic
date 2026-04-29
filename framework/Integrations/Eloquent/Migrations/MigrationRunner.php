<?php

declare(strict_types=1);

namespace Simpledynamic\Integrations\Eloquent\Migrations;

use config\ORM;
use Illuminate\Database\Capsule\Manager;

/**
 * Сервис для запуска миграций
 */
final class MigrationRunner
{
    /** @var array<array-key, object> */
    private array $newMigrations = [];

    /**
     * Загрузить миграции
     *
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function load(Manager $capsule): void
    {
        $migrationDirectories = ORM::getMigrationDirectories();

        if ($migrationDirectories === []) {
            return;
        }

        foreach ($migrationDirectories as $directory) {
            if (!is_string($directory)) {
                continue;
            }

            $this->loadByDirectory($directory, $capsule);
        }
    }

    /**
     * Загрузить миграции из директории
     */
    public function loadByDirectory(string $directory, ?Manager $capsule = null): void
    {
        if ($capsule === null) {
            $capsule = ORM::createEloquent();
        }

        /** @var array|false $files */
        $files = scandir($directory);

        if (!is_array($files)) {
            return;
        }

        foreach ($files as $file) {
            if (!is_string($file) || !preg_match('/^([0-9]+)_(.*)_table\.php$/', $file)) {
                continue;
            }

            $filePath = $directory . '/' . $file;

            if (file_exists($filePath)) {
                include_once $filePath;
            }

            $className = pathinfo($file, PATHINFO_FILENAME);

            if (!class_exists($className)) {
                continue;
            }

            $executed = $capsule::table('migrations')
                ->where('migration', $className)
                ->exists();

            if (!$executed) {
                /** @psalm-suppress MixedMethodCall */
                $this->newMigrations[] = new $className();
            }
        }
    }

    public function getMaxBatch(?Manager $capsule = null): int
    {
        if ($capsule === null) {
            $capsule = ORM::createEloquent();
        }

        /** @psalm-suppress MixedAssignment */
        $batch = $capsule::table('migrations')
            ->max('batch');

        return is_numeric($batch) ? intval($batch) : 0;
    }

    /**
     * Запустить миграции
     */
    public function run(): void
    {
        $capsule = ORM::createEloquent();

        foreach ($this->newMigrations as $migration) {
            if (!method_exists($migration, 'up')) {
                continue;
            }

            /** @psalm-suppress MixedMethodCall */
            $migration->up();

            $capsule::table('migrations')->insert([
                'migration' => basename(str_replace('\\', '/', $migration::class)),
                'batch'     => $this->getMaxBatch($capsule) + 1,
            ]);
        }
    }

    /**
     * Откатить все миграции
     */
    public function rollbackAll(): void
    {
        $capsule = ORM::createEloquent();

        /** @var array<array-key, class-string> $migrationsToRollback */
        $migrationsToRollback = $capsule::table('migrations')
            ->orderByDesc('id')
            ->pluck('migration')
            ->toArray();

        foreach ($migrationsToRollback as $migrationName) {
            /** @var class-string $fullClassName */
            $fullClassName = '\\' . str_replace('/', '\\', $migrationName);

            if (!class_exists($fullClassName) || !method_exists($fullClassName, 'down')) {
                continue;
            }

            /** @psalm-suppress MixedMethodCall */
            $migrationInstance = new $fullClassName();

            /** @psalm-suppress MixedMethodCall */
            $migrationInstance->down();

            $capsule::table('migrations')
                ->where('migration', $migrationName)
                ->delete();
        }
    }
}
