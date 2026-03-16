<?php

use App\Framework\Services\FiberTasking\Command;
use config\Commands;

if (PHP_SAPI !== 'cli') {
    exit(1);
}

$commands = Commands::getConfig();
$commandName = $commands[$_SERVER['argv'][1]];

if (is_array($commandName)) {
    foreach ($commandName as $name) {
        if (class_exists($name) && is_subclass_of($commandName, Command::class)) {
            new $commandName()->run();
        }
    }
} elseif (class_exists($commandName) && is_subclass_of($commandName, Command::class)) {
    new $commandName()->run();
}

exit(0);
