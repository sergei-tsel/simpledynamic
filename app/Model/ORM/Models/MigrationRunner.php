<?php

declare(strict_types=1);

namespace App\Model\ORM\Models;

use Illuminate\Database\Capsule\Manager;

class MigrationRunner
{
    private array $newMigrations = [];

    public function load(Manager $capsule, string $directory): void
    {
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

    public function run(Manager $capsule, string $batch): void
    {
        foreach ($this->newMigrations as $migration) {
            $migration->run();

            $capsule::table('migrations')->insert([
                'migration' => basename(str_replace('\\', '/', $migration::class)),
                'batch'     => $batch,
            ]);
        }
    }

    public function rollbackAll(Manager $capsule): void
    {
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
