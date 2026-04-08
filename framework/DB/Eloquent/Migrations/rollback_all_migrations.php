<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../../../vendor/autoload.php';

use Framework\DB\Eloquent\Migrations\MigrationRunner;

$migrationRunner = new MigrationRunner();

$migrationRunner->rollbackAll();
