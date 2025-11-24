<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../vendor/autoload.php';

use App\Model\ORM\Models\MigrationRunner;
use config\ORM;

$migrationDirectories = [
    __DIR__,
];

$capsule = ORM::createEloquentConfig();

$lastBatch = $capsule::table('migrations')
    ->max('batch') ?: 0;
$newBatch = $lastBatch + 1;

$migrationRunner = new MigrationRunner();

foreach ($migrationDirectories as $directory) {
    $migrationRunner->load($capsule, $directory);
}

$migrationRunner->run($capsule, $newBatch);
