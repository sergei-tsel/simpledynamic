<?php

declare(strict_types=1);

require __DIR__ . '/../../../vendor/autoload.php';

use config\App;
use Sympledynamic\Services\CLI\Command;

if (PHP_SAPI !== 'cli') {
    exit(1);
}

$commands = App::getConfigPart('commands') ?? [];

if (!isset($commands[$_SERVER['argv'][1]])) {
    exit(1);
}

$commandName = $commands[$_SERVER['argv'][1]];

if (is_array($commandName)) {
    foreach ($commandName as $name) {
        if (class_exists($name) && is_subclass_of($name, Command::class)) {
            $name::run();
        }
    }
} elseif (class_exists($commandName) && is_subclass_of($commandName, Command::class)) {
    $commandName::run();
}

exit(0);
