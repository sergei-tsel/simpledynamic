<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../vendor/autoload.php';

use App\Model\ORM\Models\MigrationRunner;
use config\ORM;

$migrationDirectories = [
    __DIR__,
];

$capsule = ORM::createEloquentConfig();

$migrationRunner = new MigrationRunner();

$migrationRunner->rollbackAll($capsule);
