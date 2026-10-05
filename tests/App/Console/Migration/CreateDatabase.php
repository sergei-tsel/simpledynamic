<?php

declare(strict_types=1);

namespace Test\App\Console\Migration;

use Illuminate\Database\Capsule\Manager;
use Simpledynamic\Services\CLI\Command;
use Simpledynamic\Services\Configuration\ORM;

/**
 * Команда создающая базу данных, если она не существует
 * migrate:create-db
 */
class CreateDatabase extends Command
{
    #[\Override]
    protected static string $description = 'Содаёт базу данных, если она не существует.';

    public function handle(): void
    {
        /**
         * @var array<array-key, mixed> $config
         */
        $config = ORM::getConfig();

        /** @var string $databaseName */
        $databaseName = $config['database'] ?? 'tablemap';
        $query = "CREATE DATABASE \"{$databaseName}\";";

        $config['database'] = 'postgres';
        $connectionName = 'tmp';
        $manager = new Manager();
        $manager->addConnection(config: $config, name: $connectionName);
        $manager->bootEloquent();

        $manager->getConnection(name: $connectionName)->statement($query);

        $manager->getConnection(name: $connectionName)->disconnect();
    }
}
