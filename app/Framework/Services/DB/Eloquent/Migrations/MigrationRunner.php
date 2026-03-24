<?php

declare(strict_types=1);

namespace App\Framework\Services\DB\Eloquent\Migrations;

use config\ORM;
use Illuminate\Database\Capsule\Manager;

/**
 * Сервис для запуска миграций
 */
class MigrationRunner
{
    private array $newMigrations = [];

    /**
     * Загрузить миграции
     */
    public function load(Manager $capsule): void
    {
        $migrationDirectories = ORM::getMigrationDirectories();

        foreach ($migrationDirectories as $directory) {
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

        $files = scandir($directory);

        foreach ($files as $file) {
            if (preg_match('/^(\d+)_(.*)_table\.php$/', $file)) {
                include_once $directory . '/' . $file;
                $className = pathinfo($file, PATHINFO_FILENAME);

                $executed = $capsule::table('migrations')
                    ->where('migration', $className)
                    ->exists();

                if (!$executed) {
                    $this->newMigrations[] = new $className();
                }
            }
        }
    }

    public function getMaxBatch(?Manager $capsule = null): int
    {
        if ($capsule === null) {
            $capsule = ORM::createEloquent();
        }

        return $capsule::table('migrations')
            ->max('batch') ?: 0;
    }

    /**
     * Запустить миграции
     */
    public function run(): void
    {
        $capsule = ORM::createEloquent();

        foreach ($this->newMigrations as $migration) {
            $migration->run();

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

        $migrationsToRollback = $capsule::table('migrations')
            ->orderByDesc('id')
            ->pluck('migration')
            ->toArray();

        foreach ($migrationsToRollback as $migrationName) {
            $fullClassName = '\\' . str_replace('/', '\\', $migrationName);

            $migrationInstance = new $fullClassName();
            $migrationInstance->rollback();

            $capsule::table('migrations')
                ->where('migration', $migrationName)
                ->delete();
        }
    }
}
